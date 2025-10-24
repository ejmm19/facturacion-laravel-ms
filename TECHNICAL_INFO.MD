# Instalacion de ambiente

#### Incialmente debemos instalar warden, es solo un proyecto de comandos funciona para linux, mac y windows
#### documentacion en: https://docs.warden.dev/installing.html

## Instalar ambiente
```bash
brew install wardenenv/warden/warden
# para levantar lo necesario para warden orqueste todos los servicios
warden svc up
```

```bash
# Inicializar el proyecto con warden
warden env-init facturacion-ms laravel
```

```bash
# Inicializar el proyecto con warden
warden env-init facturacion-ms laravel
```

```bash
# registrar nuestro dominio en warden
warden sign-certificate facturacion-ms.test
```

```bash
# registrar nuestro dominio en nuestro localhost
# 127.0.0.1 facturacion-ms.test
sudo nano /etc/hosts
```

```bash
# levantar el ambiente de desarrollo, contenedores
warden env up
```
```bash
# entrar en la terminal para lanzar comandos php artisan
warden shell
```

### Procesar la cola manualmente
```bash
php artisan queue:work
```

### Procesar solo la cola de facturas
```bash
php artisan queue:work --queue=facturas
```

### Procesar con timeout
```bash
php artisan queue:work --timeout=120
```

### Procesar con intentos específicos
```bash
php artisan queue:work --tries=3
```

### Ver jobs fallidos
```bash
php artisan queue:failed
```

### Reintentar job fallido
```bash
php artisan queue:retry {id}
```

### Reintentar todos los jobs fallidos
```bash
php artisan queue:retry all
```

### Limpiar jobs fallidos
```bash
php artisan queue:flush
```

### Escuchar cola en modo daemon
```bash
php artisan queue:listen




## Ejecutar Pruebas

### Todos los Tests
```bash
php artisan test
```

### Solo Tests Unitarios
```bash
php artisan test --testsuite=Unit
```

### Solo Tests de Integración
```bash
php artisan test --testsuite=Feature
```

### Test Específico
```bash
php artisan test --filter=ClienteTest
```

### Con Cobertura (requiere Xdebug)
```bash
php artisan test --coverage
```

### Tests en Paralelo
```bash
php artisan test --parallel
```
