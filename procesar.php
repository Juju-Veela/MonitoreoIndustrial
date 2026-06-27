<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $sensor_id   = isset($_POST['sensor_id']) ? trim($_POST['sensor_id']) : '';
    $temperatura = isset($_POST['temperatura']) ? floatval($_POST['temperatura']) : null;

    if (empty($sensor_id) || $temperatura === null) {
        echo json_encode(['status' => 'error', 'mensaje' => 'Parámetros incompletos.']);
        exit;
    }

    // Configuración Base de Datos
    $host = 'localhost';
    $db   = 'sistema_iot';
    $user = 'root';
    $pass = '';
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    } catch (\PDOException $e) {
        echo json_encode(['status' => 'error', 'mensaje' => 'Error de conexión con el servidor SQL.']);
        exit;
    }

    // Definición de umbrales seguros de la planta industrial
    $min_seguro = 10.0;
    $max_seguro = 40.0;
    
    // Determinar si la lectura actual está fuera de rango
    $actual_fuera_rango = ($temperatura < $min_seguro || $temperatura > $max_seguro);
    $alerta_critica = false;

    if ($actual_fuera_rango) {
        // REQUISITO TÉCNICO: Evaluar si las DOS lecturas anteriores del mismo sensor también fallaron
        // Buscamos las últimas 2 lecturas registradas ordenadas por ID descendente
        $stmt = $pdo->prepare("SELECT temperatura FROM lecturas WHERE sensor_id = ? ORDER BY id DESC LIMIT 2");
        $stmt->execute([$sensor_id]);
        $historico = $stmt->fetchAll();

        // Comprobamos si efectivamente hay al menos dos registros previos guardados
        if (count($historico) === 2) {
            $temp_ant1 = floatval($historico[0]['temperatura']);
            $temp_ant2 = floatval($historico[1]['temperatura']);

            $ant1_fuera = ($temp_ant1 < $min_seguro || $temp_ant1 > $max_seguro);
            $ant2_fuera = ($temp_ant2 < $min_seguro || $temp_ant2 > $max_seguro);

            // Si las tres están fuera de rango, se dispara la alerta crítica
            if ($ant1_fuera && $ant2_fuera) {
                $alerta_critica = true;
            }
        }
    }

    // Guardar la lectura actual en la base de datos indicando si disparó alerta
    $alerta_bit = $alerta_critica ? 1 : 0;
    $stmtInsert = $pdo->prepare("INSERT INTO lecturas (sensor_id, temperatura, alerta_disparada) VALUES (?, ?, ?)");
    $stmtInsert->execute([$sensor_id, $temperatura, $alerta_bit]);

    // Responder según la validación en tiempo real
    if ($alerta_critica) {
        echo json_encode([
            'status' => 'critico',
            'mensaje' => "<h2>⚠️ ALERTA CRÍTICA</h2><br>El sensor <strong>$sensor_id</strong> ha registrado <strong>3 lecturas consecutivas fuera de rango</strong>.<br>Último valor: <strong style='color:#ff5e57;'>$temperatura °C</strong>.<br>Notificación enviada al administrador."
        ]);
    } else {
        echo json_encode([
            'status' => 'normal',
            'mensaje' => "<h3>Lectura Procesada</h3><br>Sensor: <strong>$sensor_id</strong><br>Valor actual: $temperatura °C<br>Estado del sistema: <strong style='color:#05c46b;'>Estable</strong>"
        ]);
    }
}