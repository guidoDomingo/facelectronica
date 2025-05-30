<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\QrCodeService;

class FacturaElectronica extends Model
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'facturas_electronicas';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */    protected $fillable = [
        'cdc',
        'tipo_documento',
        'establecimiento',
        'punto',
        'numero',
        'fecha',
        'ruc_emisor',
        'razon_social_emisor',
        'ruc_receptor',
        'razon_social_receptor',
        'total',
        'impuesto',
        'moneda',
        'xml',
        'estado',
        'observacion',
        'qr_generado'
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array
     */    protected $casts = [
        'fecha' => 'datetime',
        'total' => 'decimal:2',
        'impuesto' => 'decimal:2',
        'qr_generado' => 'boolean',
    ];
    
    /**
     * Estados de la factura electrónica
     */
    const ESTADO_GENERADA = 'generada';
    const ESTADO_ENVIADA = 'enviada';
    const ESTADO_ACEPTADA = 'aceptada';
    const ESTADO_RECHAZADA = 'rechazada';
    const ESTADO_CANCELADA = 'cancelada';
    const ESTADO_INUTILIZADA = 'inutilizada';
    
    /**
     * Relación con eventos de la factura electrónica
     */
    public function eventos()
    {
        return $this->hasMany(FacturaElectronicaEvento::class, 'factura_electronica_id');
    }
    
    /**
     * Genera la representación XML de la factura electrónica
     *
     * @param array $params
     * @param array $options
     * @return string
     */
    public function generarXml($params, $options = [])
    {
        $facturaService = app('App\Services\FacturacionElectronica\FacturacionElectronicaService');
        
        // Construir datos para generar XML
        $data = [
            'tipoDocumento' => $this->tipo_documento,
            'establecimiento' => $this->establecimiento,
            'punto' => $this->punto,
            'numero' => $this->numero,
            'fecha' => $this->fecha->format('Y-m-d\TH:i:s'),
            'cliente' => [
                'contribuyente' => true,
                'ruc' => $this->ruc_receptor,
                'razonSocial' => $this->razon_social_receptor,
            ],
            'moneda' => $this->moneda,
            // Aquí se agregarían más campos según la estructura requerida
        ];
        
        return $facturaService->generateXML($params, $data, $options);
    }
      /**
     * Registra un evento asociado a la factura electrónica
     *
     * @param string $tipo
     * @param string $descripcion
     * @param array $datos
     * @return FacturaElectronicaEvento
     */
    public function registrarEvento($tipo, $descripcion, $datos = [])
    {
        // Aseguramos que los datos sean serializables
        foreach ($datos as $key => $value) {
            if (is_object($value) && method_exists($value, 'toArray')) {
                $datos[$key] = $value->toArray();
            }
        }
        
        return $this->eventos()->create([
            'tipo' => $tipo,
            'descripcion' => $descripcion,
            'datos' => $datos, // Laravel se encargará de la serialización JSON
        ]);
    }
    
    /**
     * Cancela la factura electrónica
     *
     * @param string $motivo
     * @return bool
     */
    public function cancelar($motivo)
    {
        if ($this->estado !== self::ESTADO_ACEPTADA) {
            throw new \Exception('Solo se pueden cancelar facturas en estado aceptada');
        }
        
        $this->estado = self::ESTADO_CANCELADA;
        $this->observacion = $motivo;
        $this->save();
        
        $this->registrarEvento('cancelacion', 'Factura cancelada', [
            'motivo' => $motivo,
            'fecha' => now()->format('Y-m-d\TH:i:s')
        ]);
        
        return true;
    }    /**
     * Genera el código QR para la factura según especificaciones de SIFEN
     * @return string|null Los bytes de la imagen PNG del QR o null si falla
     */
    public function generarCodigoQR()
    {
        // Verificamos que exista el XML
        if (empty($this->xml)) {
            \Illuminate\Support\Facades\Log::warning("No se puede generar el QR porque no hay XML disponible para la factura {$this->cdc}");
            return null;
        }

        // Calculamos el digest del XML (o tomamos un valor del XML si ya tiene firma)
        try {
            $digestValue = '';
            // Si el XML tiene una firma, intentamos extraer el DigestValue
            if (strpos($this->xml, '<DigestValue>') !== false) {
                preg_match('/<DigestValue>(.*?)<\/DigestValue>/', $this->xml, $matches);
                if (isset($matches[1])) {
                    $digestValue = $matches[1];
                    \Illuminate\Support\Facades\Log::info("DigestValue extraído del XML: {$digestValue}");
                }
            }
            
            // Si no hay DigestValue en el XML, calculamos un hash del contenido
            if (empty($digestValue)) {
                $digestValue = substr(hash('sha256', $this->xml), 0, 28);
                \Illuminate\Support\Facades\Log::info("DigestValue calculado: {$digestValue}");
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error al extraer DigestValue: " . $e->getMessage());
            $digestValue = substr(hash('sha256', $this->cdc), 0, 28); // Fallback a un hash del CDC
        }

        // Contamos la cantidad real de items en el XML
        $itemCount = 1; // Por defecto
        try {
            if (strpos($this->xml, '<gCamItem>') !== false) {
                $itemCount = substr_count($this->xml, '<gCamItem>');
            }
            \Illuminate\Support\Facades\Log::info("Items encontrados en XML: {$itemCount}");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error al contar items: " . $e->getMessage());
        }

        // Validar datos críticos para el QR
        if (empty($this->cdc) || empty($this->ruc_receptor)) {
            \Illuminate\Support\Facades\Log::error("Datos críticos faltantes para el QR: CDC={$this->cdc}, RUC={$this->ruc_receptor}");
            return null;
        }

        // Según las especificaciones de SIFEN, el QR debe contener ciertos datos
        // separados por ampersand (&)
        $qrData = [
            'nVersion=150',                                // Versión SIFEN
            'Id=' . $this->cdc,                           // CDC completo
            'dFeEmiDE=' . $this->fecha->format('Y-m-d'),  // Fecha emisión (formato ISO)
            'dRucRec=' . $this->ruc_receptor,             // RUC receptor
            'dTotGralOpe=' . number_format($this->total, 0, '', ''),  // Monto total sin separadores
            'dTotIVA=' . number_format($this->impuesto, 0, '', ''),    // IVA total sin separadores
            'cItems=' . $itemCount,                       // Cantidad de ítems real
            'DigestValue=' . $digestValue,                // Valor del digest
            'IdCSC=' . config('facturacion_electronica.id_csc', '0001') // ID del CSC
        ];
        
        // Generar el string para el QR
        $qrString = implode('&', $qrData);
        \Illuminate\Support\Facades\Log::info("QR String generado para CDC {$this->cdc}: {$qrString}");
        
        // Generar el QR usando nuestro servicio que maneja la compatibilidad con GD
        try {
            $qrImage = QrCodeService::generate($qrString, 'png', 200);
            if ($qrImage) {
                \Illuminate\Support\Facades\Log::info("QR generado exitosamente para CDC {$this->cdc} (" . strlen($qrImage) . " bytes)");
                return $qrImage;
            } else {
                \Illuminate\Support\Facades\Log::warning("QrCodeService retornó null para CDC {$this->cdc}");
                return null;
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error al generar código QR: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Obtiene la URL del código QR en formato base64
     * @return string|null
     */
    public function getQRBase64()
    {
        $qrImage = $this->generarCodigoQR();
        if ($qrImage) {
            return 'data:image/png;base64,' . base64_encode($qrImage);
        }
        return null;
    }
    
    /**
     * Boot del modelo para agregar eventos
     */
    protected static function boot()
    {
        parent::boot();
        
        // Trigger que se ejecuta después de guardar una factura
        static::saved(function ($factura) {
            // Si la factura tiene XML y no tiene QR generado, lo generamos automáticamente
            if (!empty($factura->xml) && empty($factura->qr_generado)) {
                try {
                    $factura->generarYGuardarQR();
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning("No se pudo generar QR automáticamente para factura {$factura->cdc}: " . $e->getMessage());
                }
            }
        });
    }

    /**
     * Genera y guarda el QR en la base de datos
     * @return bool
     */
    public function generarYGuardarQR()
    {
        try {
            if (empty($this->xml)) {
                \Illuminate\Support\Facades\Log::warning("No se puede generar QR: factura {$this->cdc} no tiene XML");
                return false;
            }

            // Generar el QR y marcar como generado
            $qrGenerado = $this->generarCodigoQR();
            if ($qrGenerado) {
                // Actualizamos sin disparar eventos para evitar loops
                $this->updateQuietly(['qr_generado' => true]);
                \Illuminate\Support\Facades\Log::info("QR generado automáticamente para factura {$this->cdc}");
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error generando QR para factura {$this->cdc}: " . $e->getMessage());
            return false;
        }
    }
}
