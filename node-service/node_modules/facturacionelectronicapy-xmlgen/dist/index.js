"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
const jsonDeMain_service_1 = __importDefault(require("./services/jsonDeMain.service"));
const jsonEventoMain_service_1 = __importDefault(require("./services/jsonEventoMain.service"));
class DE {
    constructor() {
        this.generateXMLDE = (params, data, config) => {
            return jsonDeMain_service_1.default.generateXMLDE(params, data, config);
        };
        this.generateXMLEventoCancelacion = (id, params, data, config) => {
            return jsonEventoMain_service_1.default.generateXMLEventoCancelacion(id, params, data, config);
        };
        this.generateXMLEventoInutilizacion = (id, params, data, config) => {
            return jsonEventoMain_service_1.default.generateXMLEventoInutilizacion(id, params, data, config);
        };
        this.generateXMLEventoConformidad = (id, params, data, config) => {
            return jsonEventoMain_service_1.default.generateXMLEventoConformidad(id, params, data, config);
        };
        this.generateXMLEventoDisconformidad = (id, params, data, config) => {
            return jsonEventoMain_service_1.default.generateXMLEventoDisconformidad(id, params, data, config);
        };
        this.generateXMLEventoDesconocimiento = (id, params, data, config) => {
            return jsonEventoMain_service_1.default.generateXMLEventoDesconocimiento(id, params, data, config);
        };
        this.generateXMLEventoNotificacion = (id, params, data, config) => {
            return jsonEventoMain_service_1.default.generateXMLEventoNotificacion(id, params, data, config);
        };
        this.generateXMLEventoNominacion = (id, params, data, config) => {
            return jsonEventoMain_service_1.default.generateXMLEventoNominacion(id, params, data, config);
        };
        this.generateXMLEventoActualizacionDatosTransporte = (id, params, data, config) => {
            return jsonEventoMain_service_1.default.generateXMLEventoActualizacionDatosTransporte(id, params, data, config);
        };
        this.consultarPaises = () => {
            return new Promise((resolve, reject) => {
                //Enviar Copia
                let paises = [];
                for (let index = 0; index < jsonDeMain_service_1.default.getPaises().length; index++) {
                    const pais = jsonDeMain_service_1.default.getPaises()[index];
                    paises.push(Object.assign({}, pais));
                }
                resolve(paises);
            });
        };
        this.consultarDepartamentos = () => {
            return new Promise((resolve, reject) => {
                //Enviar Copia
                let departamentos = [];
                for (let index = 0; index < jsonDeMain_service_1.default.getDepartamentos().length; index++) {
                    const dep = jsonDeMain_service_1.default.getDepartamentos()[index];
                    departamentos.push(Object.assign({}, dep));
                }
                resolve(departamentos);
            });
        };
        this.consultarDistritos = (departamento) => {
            return new Promise((resolve, reject) => {
                //Enviar Copia
                let distritos = [];
                for (let index = 0; index < jsonDeMain_service_1.default.getDistritos(departamento).length; index++) {
                    const dis = jsonDeMain_service_1.default.getDistritos(departamento)[index];
                    distritos.push(Object.assign({}, dis));
                }
                resolve(distritos);
            });
        };
        this.consultarCiudades = (distrito) => {
            return new Promise((resolve, reject) => {
                //Enviar Copia
                let ciudades = [];
                for (let index = 0; index < jsonDeMain_service_1.default.getCiudades(distrito).length; index++) {
                    const ciu = jsonDeMain_service_1.default.getCiudades(distrito)[index];
                    ciudades.push(Object.assign({}, ciu));
                }
                resolve(ciudades);
            });
        };
        this.consultarTiposRegimenes = () => {
            return new Promise((resolve, reject) => {
                let tiposRegimenes = [];
                for (let index = 0; index < jsonDeMain_service_1.default.getTiposRegimenes().length; index++) {
                    const tip = jsonDeMain_service_1.default.getTiposRegimenes()[index];
                    tiposRegimenes.push(Object.assign({}, tip));
                }
                resolve(tiposRegimenes);
            });
        };
        this.getDepartamento = (departamentoId) => {
            let departamentos = jsonDeMain_service_1.default.getDepartamento(departamentoId);
            if (departamentos.length > 0) {
                return Object.assign({}, departamentos[0]);
            }
            else {
                return null;
            }
        };
        this.getDistrito = (distritoId) => {
            let distritos = jsonDeMain_service_1.default.getDistrito(distritoId);
            if (distritos.length > 0) {
                return Object.assign({}, distritos[0]);
            }
            else {
                return null;
            }
        };
        this.getCiudad = (ciudadId) => {
            let ciudades = jsonDeMain_service_1.default.getCiudad(ciudadId);
            if (ciudades.length > 0) {
                return Object.assign({}, ciudades[0]);
            }
            else {
                return null;
            }
        };
    }
}
exports.default = new DE();
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiaW5kZXguanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi9zcmMvaW5kZXgudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6Ijs7Ozs7QUFBQSx1RkFBc0Q7QUFDdEQsK0ZBQThEO0FBRzlELE1BQU0sRUFBRTtJQUFSO1FBQ0Usa0JBQWEsR0FBRyxDQUFDLE1BQVcsRUFBRSxJQUFTLEVBQUUsTUFBcUIsRUFBZ0IsRUFBRTtZQUM5RSxPQUFPLDRCQUFTLENBQUMsYUFBYSxDQUFDLE1BQU0sRUFBRSxJQUFJLEVBQUUsTUFBTSxDQUFDLENBQUM7UUFDdkQsQ0FBQyxDQUFDO1FBRUYsaUNBQTRCLEdBQUcsQ0FBQyxFQUFVLEVBQUUsTUFBVyxFQUFFLElBQVMsRUFBRSxNQUFxQixFQUFnQixFQUFFO1lBQ3pHLE9BQU8sZ0NBQWEsQ0FBQyw0QkFBNEIsQ0FBQyxFQUFFLEVBQUUsTUFBTSxFQUFFLElBQUksRUFBRSxNQUFNLENBQUMsQ0FBQztRQUM5RSxDQUFDLENBQUM7UUFFRixtQ0FBOEIsR0FBRyxDQUFDLEVBQVUsRUFBRSxNQUFXLEVBQUUsSUFBUyxFQUFFLE1BQXFCLEVBQWdCLEVBQUU7WUFDM0csT0FBTyxnQ0FBYSxDQUFDLDhCQUE4QixDQUFDLEVBQUUsRUFBRSxNQUFNLEVBQUUsSUFBSSxFQUFFLE1BQU0sQ0FBQyxDQUFDO1FBQ2hGLENBQUMsQ0FBQztRQUVGLGlDQUE0QixHQUFHLENBQUMsRUFBVSxFQUFFLE1BQVcsRUFBRSxJQUFTLEVBQUUsTUFBcUIsRUFBZ0IsRUFBRTtZQUN6RyxPQUFPLGdDQUFhLENBQUMsNEJBQTRCLENBQUMsRUFBRSxFQUFFLE1BQU0sRUFBRSxJQUFJLEVBQUUsTUFBTSxDQUFDLENBQUM7UUFDOUUsQ0FBQyxDQUFDO1FBRUYsb0NBQStCLEdBQUcsQ0FBQyxFQUFVLEVBQUUsTUFBVyxFQUFFLElBQVMsRUFBRSxNQUFxQixFQUFnQixFQUFFO1lBQzVHLE9BQU8sZ0NBQWEsQ0FBQywrQkFBK0IsQ0FBQyxFQUFFLEVBQUUsTUFB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