# 🍽️ KDS (Kitchen Display System) para Dolibarr - Módulo "Kitchen"

**Versión:** 1.0.0  
**Dependencias:** Módulo TakePOS (versión avanzada con tablas `vol_`)  
**Licencia:** GPL-3.0  

## 📖 Descripción

El módulo **Kitchen (mykds)** es un sistema de visualización de comandas en tiempo real diseñado para cocinas de restaurantes que utilizan **Dolibarr TakePOS**.

Este sistema reemplaza las impresoras de tickets tradicionales por una pantalla digital interactiva. Permite a los cocineros ver los pedidos entrantes, organizarlos por prioridad, visualizar los platos agrupados por tiempos (Entrantes, Segundos, Postres) y marcar los platos como "Listos" (total o parcialmente) para notificar su finalización.

El módulo está altamente personalizado para trabajar con una estructura de base de datos específica (`vol_...`) y resuelve conflictos de sincronización con el TPV mediante el uso de campos de control dedicados.

---

## ✨ Características Principales

* **🖥️ Monitor en Tiempo Real:** Actualización automática cada 15 segundos para mostrar nuevas comandas sin intervención manual.
* **🧠 Lógica de "Envío a Cocina":** Solo muestra los platos cuando el camarero pulsa "Enviar/Comanda" en el TPV (filtrado por `special_code = 4`), ignorando los borradores en tiempo real.
* **🚫 Filtro Inteligente de Bebidas:** Excluye automáticamente bebidas y subcategorías (Vinos, Aguas, Refrescos) basándose en IDs de categorías configurables, mostrando solo la comida.
* **🔢 Agrupación por Tiempos:** Organiza los platos dentro de cada comanda según su tipo de servicio (Entrantes, Primeros, Segundos, Postres, Para Llevar).
* **👨‍👩‍👧‍👦 Agrupación Padre-Hijo (Variantes):** Detecta automáticamente variantes (ej: "Al punto", "Sin sal") usando `fk_parent_line` y las anida visualmente debajo del plato principal.
* **👥 Contador de Comensales:** Muestra el número de comensales por mesa en la cabecera de la tarjeta.
* **✋ Gestión de Envíos Parciales:** Permite marcar como "Listo" una cantidad específica de un plato (ej: sacar 2 de 4 entrecots) mediante botones numéricos.
* **🖱️ Drag & Drop (Arrastrar y Soltar):** Permite reordenar las comandas en pantalla para priorizar mesas manualmente. El orden se guarda en la sesión del navegador.
* **📱 Diseño Responsivo:** Interfaz basada en tarjetas con scroll horizontal (columnas) y scroll interno vertical para comandas largas, optimizado para pantallas táctiles.

---

## ⚙️ Requisitos Técnicos y Estructura de Datos

Este módulo ha sido desarrollado para una **instalación específica de Dolibarr** con tablas personalizadas.

### Tablas Utilizadas
* `vol_facture`: Cabecera del ticket (Mesa, Fecha).
* `vol_facturedet`: Líneas del pedido (Producto, Cantidad, `special_code`, `fk_parent_line`).
* `vol_facturedet_extrafields`: Estado del plato (`rectificacion`) y tipo de servicio (`servicio`).
* `vol_product` / `vol_categorie`: Para nombres y filtrado de bebidas.

### Lógica de Estado (Workflow)
Para evitar conflictos con la recarga automática del TPV (que sobrescribe el campo `servicio`), este módulo utiliza la siguiente lógica:

1.  **Plato Pendiente:**
    * `vol_facturedet.special_code` = `4` (Enviado).
    * `vol_facturedet_extrafields.rectificacion` IS `NULL`.
    * `vol_facture.fk_statut` = `0` (Ticket abierto).
2.  **Plato Listo (Hecho):**
    * Al pulsar "Listo", el KDS actualiza `vol_facturedet_extrafields.rectificacion` a `1`.
    * El KDS deja de mostrar el plato en la siguiente recarga.

---

## 🚀 Instalación

1.  **Descarga:** Copia la carpeta `kitchen` dentro del directorio `htdocs/custom/` de tu instalación de Dolibarr.
    * Ruta final: `.../htdocs/custom/kitchen/`
2.  **Estructura de Archivos:**
    ```text
    /kitchen
    ├── /core/modules/modKitchen.class.php  (Descriptor del módulo)
    ├── /css/kds_style.css                  (Estilos visuales)
    ├── kds_view.php                        (Vista principal del monitor)
    ├── kds_action.php                      (Lógica de marcar platos/actualizar BD)
    └── kds_save_order.php                  (Lógica para guardar el orden visual)
    ```
3.  **Activación:**
    * Accede a Dolibarr como Administrador.
    * Ve a **Inicio > Configuración > Módulos/Aplicaciones**.
    * Busca el módulo **"Monitor KDS"** (en la pestaña "Módulos en desarrollo" o "Otros").
    * Activa el módulo (Switch ON).
4.  **Acceso:**
    * Aparecerá una nueva entrada en el menú superior llamada **"Monitor KDS"** (o dentro del menú TPV, dependiendo de la configuración del descriptor).
    * Alternativamente, accede vía URL: `http://tu-dolibarr/custom/kitchen/kds_view.php`

---

## 🔧 Configuración (Hardcoded)

⚠️ **Importante:** Dado que este es un desarrollo a medida, los IDs de categorías y productos están definidos directamente en el código (`kds_view.php`). Si cambias tu catálogo, debes actualizar estos archivos.

### 1. Definir Categorías de Bebidas (Para ocultarlas)
Edita `kds_view.php`. Busca la consulta SQL y actualiza los IDs en la cláusula `NOT IN`:

```php
// kds_view.php
// ...
WHERE rowid IN (673, 675, 659, 680) -- IDs de Categorías de bebidas
OR fk_parent IN (673, 675, 659, 680)
// ...