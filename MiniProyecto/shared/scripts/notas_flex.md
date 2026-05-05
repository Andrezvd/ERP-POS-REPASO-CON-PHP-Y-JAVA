# 📘 NOTAS DE FLEX (Adobe Flex / Apache Flex)

## ¿Qué es Adobe Flex?

**Flex** es un framework de desarrollo de aplicaciones web enriquecidas (RIA - Rich Internet Applications) creado por Adobe. Permite construir aplicaciones web interactivas que se ejecutan en el **Adobe Flash Player** o **Adobe AIR**.

### Arquitectura
- **MXML**: Lenguaje de marcado basado en XML para definir la interfaz de usuario
- **ActionScript**: Lenguaje de programación orientado a objetos (basado en ECMAScript) para la lógica de negocio
- **Flash Player**: Runtime donde se ejecutan las aplicaciones Flex

---

## Componentes Básicos de Flex

### Contenedores
```mxml
<!-- Panel: Contenedor con título y bordes -->
<s:Panel title="Productos" width="400" height="300">
    <!-- Contenido aquí -->
</s:Panel>

<!-- VBox: Layout vertical -->
<s:VBox gap="10">
    <s:Label text="Nombre:"/>
    <s:TextInput id="txtNombre"/>
</s:VBox>

<!-- HBox: Layout horizontal -->
<s:HBox gap="5">
    <s:Button label="Guardar"/>
    <s:Button label="Cancelar"/>
</s:HBox>
```

### Controles de formulario
```mxml
<!-- TextInput: Campo de texto -->
<s:TextInput id="txtCodigo" text="PROD001"/>

<!-- NumericStepper: Selector numérico -->
<s:NumericStepper id="numCantidad" minimum="1" maximum="100" value="1"/>

<!-- ComboBox: Lista desplegable -->
<s:ComboBox id="cmbCategoria" dataProvider="{categorias}"/>

<!-- DataGrid: Tabla de datos -->
<mx:DataGrid id="dgProductos" dataProvider="{productos}">
    <mx:columns>
        <mx:DataGridColumn headerText="Código" dataField="codigo"/>
        <mx:DataGridColumn headerText="Nombre" dataField="nombre"/>
        <mx:DataGridColumn headerText="Precio" dataField="precio"/>
    </mx:columns>
</mx:DataGrid>
```

---

## Data Binding (Enlace de Datos)

El data binding conecta automáticamente la UI con los datos:

```mxml
<!-- Binding simple con {} -->
<s:Label text="Total: {precio * cantidad}"/>

<!-- Binding con [Bindable] -->
<fx:Script>
    [Bindable]
    private var nombreProducto:String = "Laptop HP";
</fx:Script>

<s:TextInput text="{nombreProducto}"/>
```

---

## Consumir APIs REST desde Flex

### HTTPService
```mxml
<fx:Declarations>
    <s:HTTPService id="productosService"
                   url="http://localhost:8000/api_productos.php"
                   method="GET"
                   result="onResult(event)"
                   fault="onFault(event)"
                   resultFormat="json"/>
</fx:Declarations>

<fx:Script>
    <![CDATA[
        import mx.rpc.events.ResultEvent;
        import mx.rpc.events.FaultEvent;
        
        private function cargarProductos():void {
            productosService.send();
        }
        
        private function onResult(event:ResultEvent):void {
            // Los datos están en event.result
            dgProductos.dataProvider = event.result;
        }
        
        private function onFault(event:FaultEvent):void {
            trace("Error: " + event.fault.message);
        }
    ]]>
</fx:Script>
```

### Enviar datos POST
```mxml
private function guardarProducto():void {
    var params:Object = {
        codigo: txtCodigo.text,
        nombre: txtNombre.text,
        precio_venta: numPrecio.value
    };
    
    var service:HTTPService = new HTTPService();
    service.url = "http://localhost:8000/api_productos.php";
    service.method = "POST";
    service.contentType = "application/json";
    service.send(JSON.stringify(params));
}
```

---

## Ejemplo Completo: CRUD de Productos en Flex

```mxml
<?xml version="1.0" encoding="utf-8"?>
<s:Application xmlns:fx="http://ns.adobe.com/mxml/2009"
               xmlns:s="library://ns.adobe.com/flex/spark"
               xmlns:mx="library://ns.adobe.com/flex/mx"
               creationComplete="init()">
    
    <fx:Script>
        <![CDATA[
            import mx.controls.Alert;
            import mx.rpc.events.ResultEvent;
            
            [Bindable]
            private var productos:Array = [];
            
            private function init():void {
                cargarProductos();
            }
            
            private function cargarProductos():void {
                productoService.send();
            }
            
            private function onProductosResult(event:ResultEvent):void {
                productos = event.result as Array;
            }
            
            private function guardarProducto():void {
                // Validar campos
                if (txtNombre.text == "" || txtPrecio.text == "") {
                    Alert.show("Complete todos los campos");
                    return;
                }
                
                var params:Object = {
                    codigo: txtCodigo.text,
                    nombre: txtNombre.text,
                    precio_venta: parseFloat(txtPrecio.text)
                };
                
                guardarService.send(params);
            }
        ]]>
    </fx:Script>
    
    <fx:Declarations>
        <s:HTTPService id="productoService"
                       url="http://localhost:8000/api_productos.php"
                       result="onProductosResult(event)"/>
        
        <s:HTTPService id="guardarService"
                       url="http://localhost:8000/api_productos.php"
                       method="POST"
                       contentType="application/json"
                       result="cargarProductos()"/>
    </fx:Declarations>
    
    <s:VGroup padding="20" gap="10" width="100%" height="100%">
        <s:Label text="Gestión de Productos" fontSize="24"/>
        
        <s:HBox gap="10">
            <s:TextInput id="txtCodigo" prompt="Código"/>
            <s:TextInput id="txtNombre" prompt="Nombre"/>
            <s:TextInput id="txtPrecio" prompt="Precio"/>
            <s:Button label="Guardar" click="guardarProducto()"/>
        </s:HBox>
        
        <mx:DataGrid dataProvider="{productos}" width="100%" height="100%">
            <mx:columns>
                <mx:DataGridColumn headerText="Código" dataField="codigo"/>
                <mx:DataGridColumn headerText="Nombre" dataField="nombre"/>
                <mx:DataGridColumn headerText="Precio" dataField="precio_venta"/>
                <mx:DataGridColumn headerText="Stock" dataField="stock_actual"/>
            </mx:columns>
        </mx:DataGrid>
    </s:VGroup>
</s:Application>
```

---

## Conceptos Clave para la Prueba Técnica

### ¿Qué es MXML?
- Lenguaje de marcado declarativo basado en XML
- Define la estructura visual de la aplicación
- Similar a HTML pero con componentes más ricos

### ¿Qué es ActionScript?
- Lenguaje de programación orientado a objetos
- Basado en ECMAScript (como JavaScript)
- Maneja eventos, lógica de negocio, comunicación con servidores

### Data Binding
- Conecta automáticamente propiedades de UI con datos
- Se usa `{}` para binding simple
- Se usa `[Bindable]` para propiedades que notifican cambios

### Componentes principales
| Componente | Uso |
|------------|-----|
| Button | Botón clickeable |
| TextInput | Campo de texto |
| TextArea | Área de texto multilínea |
| ComboBox | Lista desplegable |
| DataGrid | Tabla de datos |
| Panel | Contenedor con título |
| VBox/HBox | Layout vertical/horizontal |
| NumericStepper | Selector numérico |
| DateChooser | Selector de fecha |

### Comunicación con el servidor
- **HTTPService**: Para APIs REST (GET, POST, PUT, DELETE)
- **WebService**: Para servicios SOAP
- **RemoteObject**: Para comunicación con servidores BlazeDS/LCDS (AMF)

### Ciclo de vida de una aplicación Flex
1. `preinitialize` - Antes de crear componentes
2. `initialize` - Componentes creados pero no renderizados
3. `creationComplete` - Componentes creados y renderizados
4. `applicationComplete` - Aplicación completamente cargada

---

## ¿Por qué es relevante FLEX hoy?

Aunque Adobe Flex ha sido reemplazado por tecnologías modernas (React, Angular, Vue), muchas empresas legacy aún mantienen aplicaciones Flex, especialmente en:

- **Sistemas ERP** empresariales
- **Dashboards** financieros
- **Aplicaciones de escritorio** con Adobe AIR
- **Sistemas de gestión** internos

**Apache Flex** es la versión open source mantenida por la comunidad Apache Software Foundation.

---

## Frase clave para la entrevista

> *"Conozco la arquitectura MXML + ActionScript de Flex. Entiendo que es un framework para aplicaciones RIA (Rich Internet Applications) que se ejecutan en Flash Player. Comprendo los conceptos de data binding, componentes visuales y comunicación con servicios HTTP. Aunque mi experiencia práctica es limitada, tengo una base sólida en programación orientada a objetos y desarrollo web que me permitiría aprender Flex rápidamente."*
