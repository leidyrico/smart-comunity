# Organización de Funcionalidades - Sistema de Gestión de Comunidad

## 📋 Resumen del Sistema

Sistema de gestión integral para **Residencias Alfa** desarrollado en Laravel, que permite administrar apartamentos, recibos de gastos comunes, pagos, documentos y residentes de manera eficiente.

---

## 🏗️ Arquitectura del Sistema

### Modelos Principales

#### 1. **Apartamento** (`app/Models/Apartamento.php`)
- **Propósito**: Gestión de unidades residenciales
- **Campos clave**:
  - `numero`, `piso`, `torre`
  - `propietario`, `telefono`, `email`
  - `area_m2`, `tipo`, `estado`
  - `estatus_financiero`, `fecha_cambio_estatus`
- **Relaciones**:
  - `hasMany(Pago::class)` - Pagos realizados
  - `belongsToMany(ReciboGastoComun::class)` - Recibos asignados

#### 2. **ReciboGastoComun** (`app/Models/ReciboGastoComun.php`)
- **Propósito**: Gestión de recibos de gastos comunes
- **Campos clave**:
  - `numero_recibo`, `periodo`
  - `fecha_emision`, `fecha_vencimiento`
  - `valor_administracion`, `valor_aseo`, `valor_vigilancia`
  - `valor_mantenimiento`, `otros_conceptos`
  - `total_recibo`, `estado`
- **Estados**: `activo`, `vencido`, `anulado`

#### 3. **Pago** (`app/Models/Pago.php`)
- **Propósito**: Registro de pagos realizados
- **Campos clave**:
  - `apartamento_id`, `recibo_gasto_comun_id`
  - `monto_pagado`, `fecha_pago`
  - `metodo_pago`, `numero_comprobante`
  - `observaciones`, `estado`
- **Función**: Conecta apartamentos con recibos

#### 4. **Acta** (`app/Models/Acta.php`)
- **Propósito**: Gestión de documentos oficiales
- **Campos clave**:
  - `nro_doc`, `nombre_doc`, `fecha`
  - `descripcion`, `archivo_contenido`
  - `tipo_documento`
- **Tipos**: Correspondencia, Comunicado, Actas

#### 5. **Inquilino** (`app/Models/Inquilino.php`)
- **Propósito**: Gestión de residentes
- **Campos clave**:
  - `nombre_inquilino`, `nro_apartamento`
  - `monto_deuda`, `fecha_deuda`
  - `monto_ultimo_pago`, `fecha_ultimo_pago`

#### 6. **User** (`app/Models/User.php`)
- **Propósito**: Sistema de autenticación
- **Roles**: 
  - `ROLE_ADMIN` - Administrador
  - `ROLE_USUARIO_JUNTA_VECINOS` - Usuario de junta

---

## 🎯 Funcionalidades Principales

### 1. **Dashboard** (`DashboardController`)
- **Ruta**: `/dashboard`
- **Funciones**:
  - Estadísticas generales del sistema
  - Total de documentos y apartamentos
  - Cálculo de saldo total pendiente
  - Acciones rápidas para navegación

### 2. **Gestión de Apartamentos** (`ApartamentoController`)
- **Rutas**: `/apartamentos/*`
- **Funciones**:
  - CRUD completo de apartamentos
  - Visualización de recibos y pagos asociados
  - Gestión de propietarios e inquilinos
  - Control de estados financieros

### 3. **Gestión de Recibos** (`ReciboGastoComunController`)
- **Rutas**: `/recibos/*`
- **Funciones**:
  - Creación y edición de recibos
  - Importación masiva desde Excel
  - Asignación manual de recibos a apartamentos
  - Control de estados (activo/vencido/anulado)
  - Estadísticas de recibos vencidos

### 4. **Gestión de Pagos** (`PagoController`)
- **Rutas**: `/pagos/*`
- **Funciones**:
  - Registro de pagos individuales
  - Pago global por apartamento
  - Filtros avanzados por fecha, apartamento, método
  - Envío de comprobantes por email
  - API para consulta de recibos por apartamento

### 5. **Consulta de Deudas** (`DeudaController`)
- **Rutas**: `/deudas/*`
- **Funciones**:
  - Resumen de deudas por apartamento
  - Filtros por número y propietario
  - Exportación a Excel
  - Importación masiva de datos
  - Estadísticas financieras

### 6. **Gestión de Documentos** (`ActaController`)
- **Rutas**: `/actas/*`
- **Funciones**:
  - Subida y almacenamiento de documentos
  - Categorización por tipos
  - Descarga de archivos
  - Importación masiva desde CSV
  - Búsqueda y filtrado

### 7. **Gestión de Inquilinos** (`InquilinoController`)
- **Rutas**: `/inquilinos/*`
- **Funciones**:
  - Registro de residentes
  - Control de deudas individuales
  - Historial de pagos
  - Actualización automática de saldos

---

## 🔐 Sistema de Autenticación

### Credenciales de Acceso
- **Admin**: `admin@sc.com` / `admin123`
- **Usuario Junta**: `edrey@sc.com` / `edrey123`
- **Admin Gestión**: `admin@gestionactas.com` / `admin123`

### Middleware de Protección
- Todas las rutas principales protegidas con `auth` middleware
- Control de roles para funciones administrativas

---

## 📊 Características Técnicas

### Importación/Exportación
- **Excel**: Importación masiva de recibos y datos
- **CSV**: Importación de documentos y apartamentos
- **Exportación**: Reportes en Excel de deudas y estadísticas

### APIs Internas
- `/api/recibos-por-apartamento` - Consulta de recibos
- `/api/deudas/estadisticas` - Estadísticas financieras

### Notificaciones
- Envío de comprobantes de pago por email
- Sistema de logging para auditoría

### Interfaz de Usuario
- **Framework**: Tailwind CSS
- **Componentes**: Alpine.js
- **Layout**: Responsive con sidebar
- **Tema**: Moderno y profesional

---

## 🚀 Flujos de Trabajo Principales

### 1. **Flujo de Gestión de Recibos**
1. Crear/Importar recibos desde Excel
2. Asignar recibos a apartamentos (manual/automático)
3. Monitorear estados (activo → vencido)
4. Generar reportes de deudas

### 2. **Flujo de Pagos**
1. Seleccionar apartamento
2. Visualizar recibos pendientes
3. Registrar pago (individual/global)
4. Enviar comprobante por email
5. Actualizar estados financieros

### 3. **Flujo de Documentos**
1. Subir documento (PDF/imagen)
2. Categorizar por tipo
3. Agregar metadatos
4. Almacenar en base de datos
5. Permitir descarga

---

## 📁 Estructura de Archivos Clave

```
app/
├── Http/Controllers/
│   ├── DashboardController.php
│   ├── ApartamentoController.php
│   ├── ReciboGastoComunController.php
│   ├── PagoController.php
│   ├── DeudaController.php
│   ├── ActaController.php
│   └── InquilinoController.php
├── Models/
│   ├── Apartamento.php
│   ├── ReciboGastoComun.php
│   ├── Pago.php
│   ├── Acta.php
│   ├── Inquilino.php
│   └── User.php
└── Mail/
    └── ComprobantePago.php

resources/views/
├── dashboard.blade.php
├── apartamentos/
├── recibos/
├── pagos/
├── deudas/
├── actas/
├── inquilinos/
└── layouts/

routes/
└── web.php
```

---

## 🎯 Próximos Pasos Recomendados

1. **Optimización de Performance**
   - Implementar caché para consultas frecuentes
   - Optimizar queries con eager loading

2. **Mejoras de UX**
   - Implementar notificaciones en tiempo real
   - Agregar dashboard con gráficos

3. **Funcionalidades Adicionales**
   - Sistema de reservas de áreas comunes
   - Chat interno entre residentes
   - App móvil para residentes

4. **Seguridad**
   - Implementar 2FA
   - Auditoría completa de acciones
   - Backup automático

---

*Documento generado el {{ date('Y-m-d H:i:s') }}*
*Sistema: Residencias Alfa - Gestión de Comunidad v1.0*