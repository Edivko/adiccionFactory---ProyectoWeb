# Adicción Factory Inmobiliaria
 
Plataforma inmobiliaria con cuatro roles: visitante, comprador, vendedor y administrador.

---

## Requisitos

| Componente | Versión recomendada |
|------------|---------------------|
| Debian / Ubuntu | 12 o superior |
| Apache | 2.4 |
| PHP | 8.2 o superior |
| MariaDB | 10.6 o superior (probado en 11.8) |
| phpMyAdmin | opcional |

---

## 1. Clonar el repositorio

Coloca el proyecto dentro de la raíz de Apache:

```bash
cd /var/www/html
git clone https://github.com/TU_USUARIO/TU_REPOSITORIO.git adiccionFactoryWeb
```

---

## 2. Crear el usuario y la base de datos en MariaDB

Entra a MariaDB como root:

```bash
sudo mysql -u root -p
```

Ejecuta:

```sql
CREATE DATABASE adiccion_factory
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

CREATE USER 'eduardoWeb'@'localhost' IDENTIFIED BY 'TU_PASSWORD';

GRANT ALL PRIVILEGES ON adiccion_factory.* TO 'eduardoWeb'@'localhost';

FLUSH PRIVILEGES;
EXIT;
```

> Sustituye `TU_PASSWORD` por la contraseña que quieras usar.

---

## 3. Importar los scripts SQL

Ejecuta los tres scripts **en este orden exacto**:

```bash
cd /var/www/html/adiccionFactoryWeb

mysql -u eduardoWeb -p adiccion_factory < database/01_crear_tablas.sql
mysql -u eduardoWeb -p adiccion_factory < database/02_datos_iniciales.sql
mysql -u eduardoWeb -p adiccion_factory < database/03_modulo_contacto.sql
```

> **No ejecutes** `04_ajustes_vendedores.sql` — ese script es obsoleto y el código ya no lo utiliza.

---

## 4. Configurar la conexión a la base de datos

El archivo `config/conexion.php` **no está incluido en el repositorio** (contiene credenciales).  
Copia la plantilla y edítala:

```bash
cp config/conexion.example.php config/conexion.php
```

Abre `config/conexion.php` y ajusta los valores:

```php
$host      = 'localhost';
$usuario   = 'eduardoWeb';       // el usuario que creaste
$contrasena = 'TU_CONTRASENA';  // la contraseña que pusiste
$baseDatos = 'adiccion_factory';
$puerto    = 3306;
```

---

## 5. Crear las carpetas de subida de archivos

Las carpetas de uploads deben existir y Apache debe poder escribir en ellas.

```bash
# Fotos de inmuebles
sudo mkdir -p /var/www/html/adiccionFactoryWeb/public/recursos/uploads/inmuebles
sudo chown www-data:www-data /var/www/html/adiccionFactoryWeb/public/recursos/uploads/inmuebles
sudo chmod 755 /var/www/html/adiccionFactoryWeb/public/recursos/uploads/inmuebles

# Fotos de vendedores (upload de perfil)
sudo mkdir -p /var/www/html/adiccionFactoryWeb/public/recursos/uploads/vendedores
sudo chown www-data:www-data /var/www/html/adiccionFactoryWeb/public/recursos/uploads/vendedores
sudo chmod 755 /var/www/html/adiccionFactoryWeb/public/recursos/uploads/vendedores

# Fotos de perfil de vendedor (carpeta img)
sudo mkdir -p /var/www/html/adiccionFactoryWeb/public/recursos/img/vendedores
sudo chown www-data:www-data /var/www/html/adiccionFactoryWeb/public/recursos/img/vendedores
sudo chmod 755 /var/www/html/adiccionFactoryWeb/public/recursos/img/vendedores
```

---

## 6. Verificar que Apache tiene acceso al proyecto

Asegúrate de que el directorio del proyecto sea accesible:

```bash
sudo chown -R www-data:www-data /var/www/html/adiccionFactoryWeb
sudo find /var/www/html/adiccionFactoryWeb -type d -exec chmod 755 {} \;
sudo find /var/www/html/adiccionFactoryWeb -type f -exec chmod 644 {} \;
```

Luego vuelve a aplicar los permisos de escritura en uploads:

```bash
sudo chmod 755 /var/www/html/adiccionFactoryWeb/public/recursos/uploads/inmuebles
sudo chmod 755 /var/www/html/adiccionFactoryWeb/public/recursos/uploads/vendedores
sudo chmod 755 /var/www/html/adiccionFactoryWeb/public/recursos/img/vendedores
```

---

## 7. Reiniciar Apache

```bash
sudo systemctl restart apache2
```

---

## 8. Acceder al proyecto

Abre tu navegador en:

```
http://localhost/adiccionFactoryWeb/public/
```

### URLs principales

| Área | URL |
|------|-----|
| Inicio público | `http://localhost/adiccionFactoryWeb/public/` |
| Catálogo | `http://localhost/adiccionFactoryWeb/public/catalogo.php` |
| Login | `http://localhost/adiccionFactoryWeb/public/login.php` |
| Panel comprador | `http://localhost/adiccionFactoryWeb/comprador/` |
| Panel vendedor | `http://localhost/adiccionFactoryWeb/vendedor/` |
| Panel admin | `http://localhost/adiccionFactoryWeb/admin/` |

---

## Estructura del proyecto

```
adiccionFactoryWeb/
├── admin/          # Panel del administrador
├── app/            # Funciones PHP reutilizables
├── comprador/      # Panel del comprador
├── config/         # Conexión a la base de datos
│   ├── conexion.example.php   ← plantilla (incluida en el repo)
│   └── conexion.php           ← tu copia local (NO se sube al repo)
├── database/       # Scripts SQL
│   ├── 01_crear_tablas.sql
│   ├── 02_datos_iniciales.sql
│   └── 03_modulo_contacto.sql
├── procesos/       # Scripts que procesan formularios
├── public/         # Páginas públicas y recursos
│   ├── includes/   # Header, footer, componentes
│   └── recursos/
│       ├── css/
│       ├── img/
│       └── uploads/
│           ├── inmuebles/   ← fotos de inmuebles (escritura Apache)
│           └── vendedores/  ← fotos de perfil (escritura Apache)
└── vendedor/       # Panel del vendedor
```

---

## Roles y acceso

| Rol | Cómo obtenerlo |
|-----|----------------|
| Visitante | Acceso libre sin cuenta |
| Comprador | Registrarse en `/public/registro-comprador.php` |
| Vendedor | Registrarse en `/public/registro-vendedor.php` y esperar aprobación del admin |
| Administrador | Crear manualmente en la BD con `id_rol = 3` y `id_estado_cuenta = 2` |

### Crear un administrador manualmente

```sql
INSERT INTO Usuario (id_rol, id_estado_cuenta, nombre, apellido, correo, password_hash)
VALUES (
    3,
    2,
    'Admin',
    'Principal',
    'admin@ejemplo.com',
    '$2y$12$HASH_GENERADO_CON_password_hash'
);
```

Para generar el hash de la contraseña puedes usar este snippet PHP:

php -r "echo password_hash('admin123', PASSWORD_BCRYPT) . PHP_EOL;"
$2y$12$XuTtnb4S/FDIFMXAeaSRCuLXpjjahrNbGlTcwdq.hMwYKeiQs4XXq


---

## Tecnologías

- PHP 8.2+ (procedural, sin frameworks)
- MariaDB / MySQLi con consultas preparadas
- HTML5 + CSS3 (sin frameworks de CSS)
- Apache 2.4
