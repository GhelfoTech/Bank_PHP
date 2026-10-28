<?php

declare(strict_types=1);

namespace App\Models\UseCase;

use App\Models\Database;
use App\Models\Roles\Administrador;
use App\Models\Roles\Cliente;
use App\Models\Usuario;
use App\Traits\UseCase\OperacionesTrait;
use PDOException;

class GestionarEstadoUseCase extends Database
{
    use OperacionesTrait;

    public function execute(): void
    {
        if (empty($_SESSION['usuario_id'])) {
            header('Location: index.php?route=login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?route=panel');
            exit;
        }

        $db = $this->getConnection();
        $admin = $this->cargarUsuarioLogueado($db);

        if ($admin->getRol() !== 'administrador') {
            $_SESSION['panel_error'] = 'Solo un administrador puede cambiar el estado de usuarios y cuentas.';
            header('Location: index.php?route=panel');
            exit;
        }

        $objetivoId = filter_var($_POST['usuario_id'] ?? null, FILTER_VALIDATE_INT);
        if ($objetivoId === false || $objetivoId <= 0) {
            $_SESSION['panel_error'] = 'Identificador de usuario no válido.';
            header('Location: index.php?route=panel');
            exit;
        }

        if ($objetivoId === $admin->getId()) {
            $_SESSION['panel_error'] = 'No puede deshabilitarse a sí mismo.';
            header('Location: index.php?route=panel');
            exit;
        }

        try {
            $db->beginTransaction();

            $stmt = $db->prepare(
                'SELECT id, estado FROM usuarios WHERE id = :id LIMIT 1 FOR UPDATE'
            );
            $stmt->execute(['id' => $objetivoId]);
            $row = $stmt->fetch();

            if ($row === false) {
                $db->rollBack();
                $_SESSION['panel_error'] = 'El usuario indicado no existe.';
                header('Location: index.php?route=panel');
                exit;
            }

            $nuevoEstado = ((int) $row['estado'] === 1) ? 0 : 1;

            $updUsuario = $db->prepare(
                'UPDATE usuarios SET estado = :estado WHERE id = :id LIMIT 1'
            );
            $updUsuario->execute([
                'estado' => $nuevoEstado,
                'id' => $objetivoId,
            ]);

            $updCuentas = $db->prepare(
                'UPDATE cuentas SET estado = :estado WHERE usuario_id = :uid'
            );
            $updCuentas->execute([
                'estado' => $nuevoEstado,
                'uid' => $objetivoId,
            ]);

            $db->commit();
        } catch (PDOException) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $_SESSION['panel_error'] = 'No se pudo actualizar el estado. Intente nuevamente.';
            header('Location: index.php?route=panel');
            exit;
        }

        $accion = $nuevoEstado === 1 ? 'habilitó' : 'deshabilitó';
        $_SESSION['panel_ok'] = [
            'tipo' => 'Gestión de estado',
            'monto' => 0,
            'monto_formateado' => '—',
            'fecha' => date('d/m/Y H:i:s'),
            'descripcion' => 'Se ' . $accion . ' al usuario #' . $objetivoId . ' y sus cuentas asociadas.',
        ];

        header('Location: index.php?route=panel');
        exit;
    }
}
