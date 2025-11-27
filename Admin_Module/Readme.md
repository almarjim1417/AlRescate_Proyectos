# 🏢 Módulo Administradores de Fincas para Dolibarr

**Gestión vertical de administradores de propiedades y Portal del Cliente externo.**

Este módulo permite a las empresas que utilizan Dolibarr gestionar la figura del "Administrador de Fincas" o "Gestor de Propiedades", vinculando múltiples terceros (Comunidades/Propietarios) a un único gestor. Incluye un **Portal Web Externo** totalmente responsive para que los administradores consulten la información financiera y documental de sus comunidades en tiempo real.

---

## 🚀 Características Principales

### 🔒 Gestión Interna (Backend Dolibarr)
* **Nueva Entidad de Negocio:** Gestión completa (CRUD) de Administradores de Fincas con numeración automática personalizable.
* **Relación 1:N:** Vinculación directa de Terceros (Clientes) a un Administrador.
    * *Protección de Cartera:* El sistema impide asignar una comunidad que ya tiene gestor sin desvincularla antes.
    * *Traspaso de Cartera:* Herramienta masiva para mover comunidades de un administrador a otro.
* **Visión Consolidada:** Pestañas específicas en la ficha del administrador que muestran, de forma filtrada, toda la actividad de sus comunidades asignadas:
    * Facturas
    * Presupuestos
    * Pedidos
    * Proyectos
    * Contratos
* **Integración Nativa:**
    * Hook en la ficha de Terceros: Muestra quién es el administrador asignado con enlace directo.
    * Auditoría (Logs): Los cambios en el administrador quedan registrados en el módulo Agenda de Dolibarr.
* **Seguridad:**
    * Gestión de credenciales de acceso al portal (Usuario/Contraseña) mediante campos nativos.
    * Encriptación automática de contraseñas (Hashing `bcrypt`).

### 🌍 Portal Externo (Frontend)
* **Acceso Independiente:** Login seguro separado del panel de administración de Dolibarr.
* **Dashboard Intuitivo:**
    * KPIs (Contadores) de actividad en tiempo real.
    * Diseño adaptable a la identidad corporativa (hereda el Logo y los Colores del tema de Dolibarr).
* **Listados Completos:**
    * Buscadores y filtros por fecha, estado y comunidad.
    * Listados de: Mis Comunidades, Facturas, Presupuestos, Pedidos, Proyectos y Contratos.
* **Descarga Segura de Documentos:**
    * Descarga de PDFs (Facturas, Presupuestos, etc.) sin exponer rutas reales.
    * **Ofuscación de IDs:** URLs encriptadas (`token=...`) para evitar accesos no autorizados por enumeración.
    * Validación de propiedad en tiempo real antes de servir el archivo.

---

## 🛠️ Requisitos Técnicos

* **Dolibarr:** Versión 15.0 o superior (Probado y optimizado para v20/v21).
* **PHP:** 7.4 o superior.
* **Base de Datos:** MySQL / MariaDB.

---

## 📦 Instalación

1.  **Descomprimir:** Copie la carpeta `admfincas` dentro del directorio `/custom/` de su instalación de Dolibarr.
2.  **Activar:**
    * Vaya a *Inicio > Configuración > Módulos*.
    * Busque el módulo en la pestaña "Otros" o "Gestión de Fincas".
    * Ponga el interruptor en **ON**.
    * *Nota:* Al activar, el módulo creará automáticamente las tablas necesarias (`llx_admfincas_admfinca`) y modificará la tabla `llx_societe` para añadir la columna de relación.
3.  **Permisos:**
    * Vaya a *Usuarios y Grupos*.
    * Otorgue permisos de "Leer" y "Crear/Modificar" Administradores de Fincas a los usuarios deseados.

---

## ⚙️ Configuración

El módulo está diseñado para ser "Plug & Play", pero permite ciertos ajustes:

1.  **Numeración:**
    * Vaya a la configuración del módulo (engranaje).
    * Active el modelo de numeración "Estándar" (ADM-00001) o configure su propia máscara.
2.  **Identidad Corporativa (Portal):**
    * El Portal Externo lee automáticamente la configuración visual de su Dolibarr.
    * Para cambiar el color del Portal: *Inicio > Configuración > Entorno > Tema (Eldy) > Color de fondo para el Menú superior*.
    * Para cambiar el Logo: *Inicio > Configuración > Empresa/Organización*.

---

## 📖 Guía de Uso Rápida

### 1. Crear un Administrador
1.  Vaya al menú *Terceros > Administradores de Fincas > Nuevo*.
2.  Rellene los datos fiscales y de contacto.
3.  **Importante:** En la sección inferior, establezca el **Usuario Portal** y **Contraseña Portal** para que el cliente pueda acceder. La contraseña se encriptará al guardar.

### 2. Asignar Comunidades
1.  Entre en la ficha del Administrador creado.
2.  Vaya a la pestaña **"Terceros"**.
3.  Use el buscador "Vincular nuevo Tercero" para añadir comunidades existentes.
4.  Para mover comunidades a otro gestor, selecciónelas en la lista y use la herramienta de "Traspasar" al final de la página.

### 3. Acceso al Portal
Proporcione a su cliente la siguiente URL:
> `http://suservidor.com/custom/admfincas/public/index.php`

El cliente deberá entrar con las credenciales que usted configuró en su ficha.

---

## 🏗️ Arquitectura de Datos (Para Desarrolladores)

El módulo sigue estrictamente los estándares de desarrollo de Dolibarr.

### Estructura de Base de Datos
* **`llx_admfincas_admfinca`**: Tabla principal del objeto. Contiene datos de contacto y los campos nativos de acceso (`portal_user`, `portal_pass`).
* **`llx_societe`**: Se añade la columna `fk_admfinca` (INT) para establecer la relación 1:N.

### Seguridad Implementada
1.  **CSRF Protection:** Todos los formularios (incluidos buscadores) incluyen tokens de seguridad (`newToken()`).
2.  **SQL Injection:** Uso de funciones `natural_search` y escapeo de variables.
3.  **Password Hashing:** Las contraseñas nunca se guardan en texto plano. Se utiliza `password_hash()` y `password_verify()` en el login.
4.  **URL Obfuscation:** En el portal público, los enlaces de descarga no muestran el ID (`download.php?id=15`). Se utiliza un sistema de encriptación AES-256 para generar tokens temporales.
5.  **Access Control:** Cada descarga de archivo verifica mediante SQL que el usuario logueado es realmente el gestor de la comunidad propietaria del documento.

### Estructura de Archivos Clave