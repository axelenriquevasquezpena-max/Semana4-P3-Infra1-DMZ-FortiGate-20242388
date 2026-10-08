<?php
session_start();
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));
function h($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}
$c = require '/etc/lab-app/database.php';
$mensaje = '';
try {
    $db = new PDO(
        "mysql:host={$c['host']};dbname={$c['database']};charset=utf8mb4",
        $c['user'], $c['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
         PDO::ATTR_EMULATE_PREPARES => false]
    );
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!hash_equals($_SESSION['csrf'], (string)($_POST['csrf'] ?? ''))) {
            http_response_code(403);
            exit('Solicitud no válida. Recarga la página.');
        }
        $id = filter_var($_POST['producto'] ?? '', FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]);
        $cantidad = filter_var($_POST['cantidad'] ?? '', FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1, 'max_range' => 1000000]]);
        if ($id === false || $cantidad === false) {
            $mensaje = 'Selecciona un producto y una cantidad válida.';
        } else {
            $db->beginTransaction();
            $q = $db->prepare(
                'SELECT id,precio,existencias FROM productos WHERE id=? FOR UPDATE'
            );
            $q->execute([$id]);
            $p = $q->fetch(PDO::FETCH_ASSOC);
            if (!$p || $p['existencias'] < $cantidad) {
                $db->rollBack();
                $mensaje = 'No hay existencias suficientes para esta venta.';
            } elseif ((float)$p['precio'] < 0) {
                $db->rollBack();
                $mensaje = 'El producto tiene un precio inválido.';
            } else {
                $q = $db->prepare(
                    'UPDATE productos SET existencias=existencias-? WHERE id=?'
                );
                $q->execute([$cantidad, $id]);
                $q = $db->prepare(
                    'INSERT INTO ventas
                     (producto_id,cantidad,precio_unitario,total)
                     SELECT id,?,precio,precio * ? FROM productos WHERE id=?'
                );
                $q->execute([$cantidad, $cantidad, $id]);
                $db->commit();
                $_SESSION['csrf'] = bin2hex(random_bytes(32));
                header('Location: /caja.php?venta=1');
                exit;
            }
        }
    }
    $productos = $db->query(
        'SELECT id,nombre,precio,existencias FROM productos ORDER BY nombre'
    )->fetchAll(PDO::FETCH_ASSOC);
    $ventas = $db->query(
        'SELECT v.id,p.nombre,v.cantidad,v.total,v.fecha
         FROM ventas v JOIN productos p ON p.id=v.producto_id
         ORDER BY v.id DESC LIMIT 10'
    )->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    error_log('Caja: error DB '.$e->getCode());
    http_response_code(503);
    exit('No se pudo completar la operación. Comprueba el historial antes de reintentar.');
}
?>
<!doctype html>
<html lang="es">
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Sistema de Caja</title>
<style>
body{font:16px Arial;background:#eef2f6;color:#172b40;margin:0}
main{max-width:950px;margin:40px auto;padding:28px;background:white;border-radius:12px}
h1{color:#1855a0}form{display:flex;gap:12px;flex-wrap:wrap;margin:25px 0}
label{display:grid;gap:6px}input,select,button{padding:10px;font:inherit;max-width:100%}
button{background:#1855a0;color:white;border:0;border-radius:5px;cursor:pointer}
table{width:100%;border-collapse:collapse}th,td{padding:12px;text-align:left;border-bottom:1px solid #ddd}
th{background:#e8effa}.error{color:#a12424}.ok{color:#17644c;font-weight:bold}
</style>
<main>
<h1>Sistema de Caja</h1>
<p>Servidor: WEB-CAJA · Base de datos central: DB-P2</p>
<p class="error"><?= h($mensaje) ?></p>
<?php if (isset($_GET['venta'])): ?>
<p class="ok">Venta registrada y existencias actualizadas.</p>
<?php endif; ?>
<h2>Registrar venta</h2>
<form method="post">
<input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>">
<label>Producto
<select name="producto" required>
<option value="">Selecciona un producto</option>
<?php foreach ($productos as $p): ?>
<option value="<?= h($p['id']) ?>">
<?= h($p['nombre']) ?> — RD$ <?= h($p['precio']) ?>
— Disponibles: <?= h($p['existencias']) ?>
</option>
<?php endforeach; ?>
</select>
</label>
<label>Cantidad<input name="cantidad" type="number"
min="1" max="1000000" value="1" required></label>
<button>Registrar venta</button>
</form>
<h2>Últimas 10 ventas</h2>
<table>
<tr><th>ID</th><th>Producto</th><th>Cantidad</th><th>Total RD$</th><th>Fecha</th></tr>
<?php foreach ($ventas as $v): ?>
<tr>
<td><?= h($v['id']) ?></td><td><?= h($v['nombre']) ?></td>
<td><?= h($v['cantidad']) ?></td><td><?= h($v['total']) ?></td>
<td><?= h($v['fecha']) ?></td>
</tr>
<?php endforeach; ?>
</table>
</main>
</html>
