<?php
header('Content-Type: application/json');
date_default_timezone_set('America/Argentina/Buenos_Aires'); // Zona horaria local

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $accion  = isset($_POST['accion']) ? $_POST['accion'] : '';
    $patente = isset($_POST['patente']) ? strtoupper(trim($_POST['patente'])) : '';

    if (empty($patente)) {
        echo json_encode(['status' => 'error', 'mensaje' => 'La patente es obligatoria.']);
        exit;
    }

    // Configuración Base de Datos
    $host = 'localhost';
    $db   = 'sistema_estacionamiento';
    $user = 'root';
    $pass = '';
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    } catch (\PDOException $e) {
        echo json_encode(['status' => 'error', 'mensaje' => 'Error de conexión SQL.']);
        exit;
    }

    // --- LÓGICA DE INGRESO ---
    if ($accion === 'ingreso') {
        // Verificar si el coche ya está adentro (estado activo)
        $stmt = $pdo->prepare("SELECT id FROM registros WHERE patente = ? AND estado = 'activo'");
        $stmt->execute([$patente]);
        if ($stmt->fetch()) {
            echo json_encode(['status' => 'error', 'mensaje' => "El vehículo con patente <strong>$patente</strong> ya se encuentra dentro del establecimiento."]);
            exit;
        }

        // Insertar registro de entrada
        $stmtInsert = $pdo->prepare("INSERT INTO registros (patente, hora_ingreso) VALUES (?, NOW())");
        $stmtInsert->execute([$patente]);

        echo json_encode([
            'status' => 'success',
            'mensaje' => "<h3>¡Ingreso Registrado!</h3><br>Vehículo: <strong>$patente</strong><br>Hora de entrada: " . date('H:i:s')
        ]);
    } 
    
    // --- LÓGICA DE EGRESO (REQUISITO TÉCNICO) ---
    elseif ($accion === 'egreso') {
        // Buscar la estadía activa de esa patente
        $stmt = $pdo->prepare("SELECT id, hora_ingreso FROM registros WHERE patente = ? AND estado = 'activo'");
        $stmt->execute([$patente]);
        $registro = $stmt->fetch();

        if (!$registro) {
            echo json_encode(['status' => 'error', 'mensaje' => "No se encontró ningún vehículo activo con la patente <strong>$patente</strong>."]);
            exit;
        }

        $id_registro = $registro['id'];
        $hora_ingreso = new DateTime($registro['hora_ingreso']);
        $hora_egreso = new DateTime(); // Hora actual

        // Calcular la diferencia total en minutos
        $intervalo = $hora_ingreso->diff($hora_egreso);
        $total_minutos = ($intervalo->days * 24 * 60) + ($intervalo->h * 60) + $intervalo->i;

        // --- ALGORITMO DE TARIFADO SOLICITADO ---
        $tarifa_primera_hora = 1000.00; // Tarifa fija por los primeros 60 min
        $precio_fraccion_15min = 300.00; // Tarifa diferenciada por bloque extra
        $monto_final = 0.00;

        if ($total_minutos <= 60) {
            // Caso 1: Estuvo una hora o menos
            $monto_final = $tarifa_primera_hora;
        } else {
            // Caso 2: Superó la primera hora
            $minutos_excedentes = $total_minutos - 60;
            
            // Requisito: Calcular fracciones de 15 minutos (ej: 16 minutos son 2 fracciones)
            $fracciones_excedentes = ceil($minutos_excedentes / 15);
            
            $monto_final = $tarifa_primera_hora + ($fracciones_excedentes * $precio_fraccion_15min);
        }

        // Guardar el cierre del ticket en la base de datos
        $stmtUpdate = $pdo->prepare("UPDATE registros SET hora_egreso = NOW(), total_minutos = ?, monto_pago = ?, estado = 'finalizado' WHERE id = ?");
        $stmtUpdate->execute([$total_minutos, $monto_final, $id_registro]);

        echo json_encode([
            'status' => 'success',
            'mensaje' => "<h3>Resumen de Estadía</h3><br>Vehículo: <strong>$patente</strong><br>Tiempo total: $total_minutos min.<br><strong>Total a abonar: $$monto_final</strong>"
        ]);
    }
}