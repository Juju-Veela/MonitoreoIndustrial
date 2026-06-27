Monitoreo Industrial IoT con Validación de Umbrales (Caso 6.3)

Este proyecto permite simular el envío de telemetría de un sensor industrial hacia el servidor. El sistema analiza los registros en tiempo real e implementa una validación automatizada que dispara una alerta crítica si los valores se encuentran fuera de rango durante tres lecturas consecutivas.

🚀 ¿Para qué sirve? El sistema recrea los esquemas de seguridad de una planta automatizada:
* El dispositivo IoT (simulado por el cliente) transmite de forma constante la ID del sensor y su temperatura actual.
* Los datos se registran inmediatamente en la tabla lecturas de la base de datos de manera contable.
* El servidor evalúa las últimas métricas del sensor bajo un umbral de seguridad operativa establecido (Rango seguro: entre 10°C y 40°C).
  * Si la temperatura actual viola el rango, analiza los dos registros anteriores guardados de ese mismo dispositivo. Si ambas lecturas previas también estuvieron fuera del rango (sumando 3 consecutivas), la columna alerta_disparada pasa a marcarse con "1" y se despliega un aviso rojo de emergencia.
  * Si la lectura está dentro del margen seguro, o si las anomalías previas no completan la serie de tres consecutivas, el estado se mantiene "Estable" y muestra un reporte de control ordinario.

🛠️ Tecnologías utilizadas Para garantizar un desarrollo limpio y profesional, las tecnologías se encuentran completamente separadas sin mezclarse:
* Frontend: HTML5 (Estructura de consola), CSS3 (Diseño oscuro tipo dashboard) y JavaScript Vanilla (Envío asíncrono con Fetch API e inyección dinámica de estilos de alerta).
* Backend: PHP (Lógica del servidor, análisis de arrays históricos y validación matemática de umbrales).
* Base de Datos: MySQL / MariaDB (Persistencia de datos mediante consultas preparadas con PDO).

💻 Instrucciones de Instalación y Configuración
Seguí estos pasos para instalar el entorno local y ejecutar el proyecto desde cero utilizando XAMPP:

---> Paso 1: Instalar XAMPP
* Descargá XAMPP desde su página oficial (apachefriends.org).
* Instalalo en tu computadora siguiendo los pasos por defecto (usualmente se instala en C:\xampp).

---> Paso 2: Ubicar el proyecto
* Copiá tu carpeta del proyecto (por ejemplo, monitoreoIndustrial) con todos sus archivos (index.html, style.css, script.js, procesar.php).
* Pegala dentro de la ruta raíz del servidor local de XAMPP: C:\xampp\htdocs

---> Iniciar los servicios de XAMPP
* Abrí el panel de control de XAMPP Control Panel.
* Dale clic al botón Start en los módulos de Apache y MySQL. Ambos deben ponerse en color verde.

🗄️ Cómo Crear y Ver las Tablas en la Base de Datos Crear la Base de Datos
* Abrí tu navegador e ingresá a: http://localhost/phpmyadmin/
* En la barra superior, hacé clic en la pestaña SQL.
* Pegá el siguiente código y presioná el botón Continuar:

CREATE DATABASE IF NOT EXISTS sistema_iot; USE sistema_iot;
CREATE TABLE IF NOT EXISTS lecturas ( id INT AUTO_INCREMENT PRIMARY KEY, sensor_id VARCHAR(20) NOT NULL, temperatura DECIMAL(5, 2) NOT NULL, fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP, alerta_disparada TINYINT(1) DEFAULT 0 );

(dar formato con el boton "formato")

🤓 Visualizar los Datos Registrados Cada vez que envíes el formulario, podés auditar qué pasó en la base de datos desde la misma interfaz de phpMyAdmin:
* En la barra lateral izquierda, hacé clic sobre la base de datos sistema_iot.
* Seleccioná la tabla lecturas.
* Hacé clic en la pestaña Examinar (Browse) en la parte superior para auditar las filas, los valores de temperatura y verificar qué registros tienen el bit alerta_disparada activo en "1".

🏃 El momento de la Verdad: Cómo Ejecutarlo ⚠️ IMPORTANTE: No abras el archivo haciendo doble clic directo desde tus carpetas (file:///), ya que el navegador bloqueará la comunicación con el servidor por políticas de seguridad.
Para ejecutarlo correctamente, abrí tu navegador e ingresá a la URL local: 👉 http://localhost/monitoreoIndustrial/index.html
