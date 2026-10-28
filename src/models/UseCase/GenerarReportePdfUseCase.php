<?php

declare(strict_types=1);

namespace App\Models\UseCase;

use App\Models\Database;
use App\Models\Roles\Administrador;
use App\Models\Roles\Cliente;
use App\Models\Usuario;
use Dompdf\Dompdf;
use Dompdf\Options;

class GenerarReportePdfUseCase extends Database
{
    public function execute(): void
    {
        if (empty($_SESSION['usuario_id'])) {
            header('Location: index.php?route=login');
            exit;
        }

        $db = $this->getConnection();
        $admin = $this->cargarUsuarioLogueado($db);

        if ($admin->getRol() !== 'administrador') {
            $_SESSION['panel_error'] = 'Solo un administrador puede generar reportes PDF.';
            header('Location: index.php?route=panel');
            exit;
        }

        $cuentaId = filter_var($_GET['cuenta_id'] ?? null, FILTER_VALIDATE_INT);
        if ($cuentaId === false || $cuentaId <= 0) {
            $_SESSION['panel_error'] = 'Identificador de cuenta no válido.';
            header('Location: index.php?route=panel');
            exit;
        }

        $cuentaStmt = $db->prepare(
            'SELECT c.id, c.usuario_id, c.numero_cuenta, c.saldo, c.estado,
                    u.cedula, u.nombres, u.email, u.rol, u.estado AS usuario_estado
             FROM cuentas c
             INNER JOIN usuarios u ON u.id = c.usuario_id
             WHERE c.id = :id
             LIMIT 1'
        );
        $cuentaStmt->execute(['id' => $cuentaId]);
        $cuenta = $cuentaStmt->fetch();

        if ($cuenta === false) {
            $_SESSION['panel_error'] = 'No se encontró la cuenta solicitada.';
            header('Location: index.php?route=panel');
            exit;
        }

        $movStmt = $db->prepare(
            'SELECT id, tipo, monto, fecha, descripcion
             FROM movimientos
             WHERE cuenta_id = :cuenta_id
             ORDER BY fecha DESC, id DESC'
        );
        $movStmt->execute(['cuenta_id' => $cuentaId]);
        $movimientos = $movStmt->fetchAll();

        if (!class_exists(Dompdf::class)) {
            $_SESSION['panel_error'] = 'Falta Dompdf. En la raíz del proyecto ejecute: composer require dompdf/dompdf';
            header('Location: index.php?route=panel');
            exit;
        }

        $html = $this->construirHtml($cuenta, $movimientos, $admin->getNombres());

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $numeroSeguro = preg_replace('/[^A-Za-z0-9\-]/', '', (string) $cuenta['numero_cuenta']) ?: (string) $cuentaId;
        $nombreArchivo = 'reporte-cuenta-' . $numeroSeguro . '.pdf';

        $dompdf->stream($nombreArchivo, ['Attachment' => true]);
        exit;
    }

    /**
     * @param array<string, mixed> $cuenta
     * @param list<array<string, mixed>> $movimientos
     */
    private function construirHtml(array $cuenta, array $movimientos, string $generadoPor): string
    {
        $numero = htmlspecialchars((string) $cuenta['numero_cuenta'], ENT_QUOTES, 'UTF-8');
        $titular = htmlspecialchars((string) $cuenta['nombres'], ENT_QUOTES, 'UTF-8');
        $cedula = htmlspecialchars((string) $cuenta['cedula'], ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars((string) $cuenta['email'], ENT_QUOTES, 'UTF-8');
        $rol = htmlspecialchars(ucfirst((string) $cuenta['rol']), ENT_QUOTES, 'UTF-8');
        $saldo = number_format((float) $cuenta['saldo'], 2);
        $estadoCuenta = ((int) $cuenta['estado'] === 1) ? 'Activa' : 'Inactiva';
        $estadoUsuario = ((int) $cuenta['usuario_estado'] === 1) ? 'Activo' : 'Inactivo';
        $fechaReporte = date('d/m/Y H:i:s');
        $adminNombre = htmlspecialchars($generadoPor, ENT_QUOTES, 'UTF-8');
        $totalMovs = count($movimientos);

        $filas = '';
        if ($movimientos === []) {
            $filas = '<tr><td colspan="5" style="text-align:center;padding:16px;color:#64748b;">Sin movimientos registrados.</td></tr>';
        } else {
            foreach ($movimientos as $mov) {
                $tipoRaw = (string) $mov['tipo'];
                $tipo = match ($tipoRaw) {
                    'deposito' => 'Depósito',
                    'retiro' => 'Retiro',
                    'transferencia' => 'Transferencia',
                    default => ucfirst($tipoRaw),
                };
                $color = match ($tipoRaw) {
                    'deposito' => '#059669',
                    'retiro' => '#dc2626',
                    'transferencia' => '#b45309',
                    default => '#334155',
                };
                $filas .= '<tr>'
                    . '<td>' . htmlspecialchars((string) $mov['id'], ENT_QUOTES, 'UTF-8') . '</td>'
                    . '<td>' . htmlspecialchars((string) $mov['fecha'], ENT_QUOTES, 'UTF-8') . '</td>'
                    . '<td style="color:' . $color . ';font-weight:700;">' . htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8') . '</td>'
                    . '<td style="text-align:right;">$' . number_format((float) $mov['monto'], 2) . '</td>'
                    . '<td>' . htmlspecialchars((string) $mov['descripcion'], ENT_QUOTES, 'UTF-8') . '</td>'
                    . '</tr>';
            }
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de movimientos</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; font-size: 11px; margin: 0; }
        .header { background: #0f172a; color: #f8fafc; padding: 22px 28px; }
        .header h1 { margin: 0 0 4px; font-size: 20px; }
        .header p { margin: 0; color: #94a3b8; font-size: 10px; }
        .meta { padding: 18px 28px 8px; }
        .cards { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .cards td { width: 50%; vertical-align: top; padding: 0 8px 10px 0; }
        .card { border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 12px; }
        .label { color: #64748b; font-size: 9px; text-transform: uppercase; letter-spacing: .6px; }
        .value { font-size: 12px; font-weight: 700; margin-top: 2px; }
        table.movs { width: 100%; border-collapse: collapse; margin: 0 28px 24px; width: calc(100% - 56px); }
        table.movs th { background: #1e293b; color: #f8fafc; font-size: 9px; text-transform: uppercase; padding: 8px; text-align: left; }
        table.movs td { border-bottom: 1px solid #e2e8f0; padding: 8px; }
        .footer { padding: 0 28px 20px; color: #64748b; font-size: 9px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Bank Josstor</h1>
        <p>Reporte de movimientos de cuenta · Generado por {$adminNombre} el {$fechaReporte}</p>
    </div>
    <div class="meta">
        <table class="cards">
            <tr>
                <td>
                    <div class="card">
                        <div class="label">Titular</div>
                        <div class="value">{$titular}</div>
                        <div class="label" style="margin-top:8px;">Cédula / Email / Rol</div>
                        <div>{$cedula} · {$email} · {$rol}</div>
                    </div>
                </td>
                <td>
                    <div class="card">
                        <div class="label">Cuenta</div>
                        <div class="value">{$numero}</div>
                        <div class="label" style="margin-top:8px;">Saldo actual</div>
                        <div class="value">\${$saldo}</div>
                        <div>Usuario: {$estadoUsuario} · Cuenta: {$estadoCuenta} · Movimientos: {$totalMovs}</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <table class="movs">
        <thead>
            <tr>
                <th>ID</th>
                <th>Fecha</th>
                <th>Tipo</th>
                <th style="text-align:right;">Monto</th>
                <th>Descripción</th>
            </tr>
        </thead>
        <tbody>
            {$filas}
        </tbody>
    </table>
    <div class="footer">Documento confidencial de uso interno. No modifica el esquema de datos ni el saldo de la cuenta.</div>
</body>
</html>
HTML;
    }

    private function cargarUsuarioLogueado(\PDO $db): Usuario
    {
        $id = (int) $_SESSION['usuario_id'];
        $stmt = $db->prepare(
            'SELECT id, cedula, nombres, email, password, rol, estado
             FROM usuarios
             WHERE id = :id AND estado = 1
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if ($row === false) {
            $_SESSION = [];
            session_destroy();
            header('Location: index.php?route=login');
            exit;
        }

        if ((string) $row['rol'] === 'administrador') {
            return new Administrador(
                (int) $row['id'],
                (string) $row['cedula'],
                (string) $row['nombres'],
                (string) $row['email'],
                (string) $row['password'],
                (bool) $row['estado']
            );
        }

        return new Cliente(
            (int) $row['id'],
            (string) $row['cedula'],
            (string) $row['nombres'],
            (string) $row['email'],
            (string) $row['password'],
            (bool) $row['estado']
        );
    }
}
