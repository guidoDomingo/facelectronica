<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacturaElectronicaEvento extends Model
{
    use HasFactory;
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'factura_electronica_eventos';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'factura_electronica_id',
        'tipo',
        'descripcion',
        'datos',
        'xml',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'datos' => 'array',
    ];
    
    /**
     * Tipos de eventos
     */
    const TIPO_CANCELACION = 'cancelacion';
    const TIPO_INUTILIZACION = 'inutilizacion';
    const TIPO_CONFORMIDAD = 'conformidad';
    const TIPO_DISCONFORMIDAD = 'disconformidad';
    const TIPO_DESCONOCIMIENTO = 'desconocimiento';
    const TIPO_NOTIFICACION = 'notificacion';
    
    /**
     * Relación con la factura electrónica
     */
    public function facturaElectronica()
    {
        return $this->belongsTo(FacturaElectronica::class, 'factura_electronica_id');
    }
    
    /**
     * Genera la representación XML del evento
     *
     * @param array $params
     * @param array $options
     * @return string
     */
    public function generarXml($params, $options = [])
    {
        $facturaService = app('App\Services\FacturacionElectronica\FacturacionElectronicaService');
        $id = $this->id ?? random_int(1, 1000);
        
        switch ($this->tipo) {
            case self::TIPO_CANCELACION:
                return $facturaService->generateXMLEventoCancelacion($id, $params, $this->datos, $options);
            case self::TIPO_INUTILIZACION:
                return $facturaService->generateXMLEventoInutilizacion($id, $params, $this->datos, $options);
            case self::TIPO_CONFORMIDAD:
                return $facturaService->generateXMLEventoConformidad($id, $params, $this->datos, $options);
            case self::TIPO_DISCONFORMIDAD:
                return $facturaService->generateXMLEventoDisconformidad($id, $params, $this->datos, $options);
            case self::TIPO_DESCONOCIMIENTO:
                return $facturaService->generateXMLEventoDesconocimiento($id, $params, $this->datos, $options);
            case self::TIPO_NOTIFICACION:
                return $facturaService->generateXMLEventoNotificacion($id, $params, $this->datos, $options);
            default:
                throw new \Exception("Tipo de evento no soportado: {$this->tipo}");
        }
    }
}
