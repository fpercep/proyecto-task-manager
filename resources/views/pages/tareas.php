<?php
/**
 * Página: Todas las Tareas
 * - Formularios de creación (Proyecto + Tarea)
 * - Tablas de listado con datos de la BD
 */

// Ruta base del proyecto (relativa desde views/pages/)
$configPath = __DIR__ . '/../../../config/database.php';

// Variables de estado
$msgExito = '';
$msgError = '';
$proyectos = [];
$tareas = [];

try {
    require_once $configPath;
    $pdo = getConnection();

    // ── Procesar formularios POST ──────────────────────────
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // Crear Proyecto
        if (isset($_POST['accion']) && $_POST['accion'] === 'crear_proyecto') {
            $nombre = trim($_POST['proyecto_nombre'] ?? '');
            $descripcion = trim($_POST['proyecto_descripcion'] ?? '');

            if ($nombre === '') {
                $msgError = 'El nombre del proyecto es obligatorio.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO proyectos (nombre, descripcion) VALUES (:nombre, :descripcion)');
                $stmt->execute([
                    ':nombre'      => $nombre,
                    ':descripcion' => $descripcion ?: null,
                ]);
                $msgExito = "Proyecto «{$nombre}» creado correctamente.";
            }
        }

        // Crear Tarea
        if (isset($_POST['accion']) && $_POST['accion'] === 'crear_tarea') {
            $titulo      = trim($_POST['tarea_titulo'] ?? '');
            $descripcion = trim($_POST['tarea_descripcion'] ?? '');
            $estado      = $_POST['tarea_estado'] ?? 'pendiente';
            $proyectoId  = $_POST['tarea_proyecto_id'] ?? '';
            $fechaVenc   = $_POST['tarea_fecha'] ?? '';

            if ($titulo === '') {
                $msgError = 'El título de la tarea es obligatorio.';
            } else {
                $stmt = $pdo->prepare(
                    'INSERT INTO tareas (titulo, descripcion, estado, proyecto_id, fecha_vencimiento)
                     VALUES (:titulo, :descripcion, :estado, :proyecto_id, :fecha_vencimiento)'
                );
                $stmt->execute([
                    ':titulo'            => $titulo,
                    ':descripcion'       => $descripcion ?: null,
                    ':estado'            => $estado,
                    ':proyecto_id'       => $proyectoId !== '' ? (int)$proyectoId : null,
                    ':fecha_vencimiento' => $fechaVenc !== '' ? $fechaVenc : null,
                ]);
                $msgExito = "Tarea «{$titulo}» creada correctamente.";
            }
        }

        // Eliminar Proyecto
        if (isset($_POST['accion']) && $_POST['accion'] === 'eliminar_proyecto') {
            $proyectoId = (int)($_POST['proyecto_id'] ?? 0);
            if ($proyectoId > 0) {
                $stmt = $pdo->prepare('DELETE FROM proyectos WHERE id = :id');
                $stmt->execute([':id' => $proyectoId]);
                $msgExito = "Proyecto eliminado correctamente.";
            } else {
                $msgError = "ID de proyecto no válido.";
            }
        }

        // Eliminar Tarea
        if (isset($_POST['accion']) && $_POST['accion'] === 'eliminar_tarea') {
            $tareaId = (int)($_POST['tarea_id'] ?? 0);
            if ($tareaId > 0) {
                $stmt = $pdo->prepare('DELETE FROM tareas WHERE id = :id');
                $stmt->execute([':id' => $tareaId]);
                $msgExito = "Tarea eliminada correctamente.";
            } else {
                $msgError = "ID de tarea no válido.";
            }
        }
    }

    // ── Consultar datos ────────────────────────────────────
    $proyectos = $pdo->query('SELECT * FROM proyectos ORDER BY created_at DESC')->fetchAll();
    $tareas = $pdo->query(
        'SELECT t.*, p.nombre AS proyecto_nombre
         FROM tareas t
         LEFT JOIN proyectos p ON t.proyecto_id = p.id
         ORDER BY t.created_at DESC'
    )->fetchAll();

} catch (PDOException $e) {
    $msgError = 'Error de conexión a la base de datos. Asegúrate de que MySQL esté corriendo y la BD exista.';
    // En desarrollo puedes descomentar la siguiente línea para ver el error:
    // $msgError .= ' Detalle: ' . $e->getMessage();
}

// Helper para escapar output
function esc(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// Mapeo de estados a estilos de badge
$estadoBadge = [
    'pendiente'   => 'bg-amber-50 text-amber-700 border-amber-200',
    'en_progreso' => 'bg-sky-50 text-sky-700 border-sky-200',
    'completada'  => 'bg-emerald-50 text-emerald-700 border-emerald-200',
];
$estadoLabel = [
    'pendiente'   => 'Pendiente',
    'en_progreso' => 'En Progreso',
    'completada'  => 'Completada',
];
?>

<div class="max-w-full mx-auto space-y-8">

    <!-- ═══ Header ═══ -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Todas las Tareas</h1>
            <p class="text-sm text-gray-500 mt-1">Gestiona tus proyectos y tareas desde aquí.</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-orange-50 text-orange-600 text-xs font-semibold rounded-full border border-orange-200">
                <i data-lucide="folder-kanban" class="w-3.5 h-3.5"></i>
                <?php echo count($proyectos); ?> proyectos
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 text-blue-600 text-xs font-semibold rounded-full border border-blue-200">
                <i data-lucide="list-checks" class="w-3.5 h-3.5"></i>
                <?php echo count($tareas); ?> tareas
            </span>
        </div>
    </div>

    <!-- ═══ Mensajes de feedback ═══ -->
    <?php if ($msgExito): ?>
        <div id="msg-exito" class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-sm animate-fade-in">
            <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-500 shrink-0"></i>
            <span><?php echo esc($msgExito); ?></span>
        </div>
    <?php endif; ?>
    <?php if ($msgError): ?>
        <div id="msg-error" class="flex items-center gap-3 p-4 bg-red-50 border border-red-200 rounded-xl text-red-800 text-sm animate-fade-in">
            <i data-lucide="alert-circle" class="w-5 h-5 text-red-500 shrink-0"></i>
            <span><?php echo esc($msgError); ?></span>
        </div>
    <?php endif; ?>

    <!-- ═══ Formularios de creación ═══ -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- ── Formulario: Nuevo Proyecto ── -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-orange-50 to-white">
                <h2 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                    <div class="p-1.5 bg-orange-100 rounded-lg">
                        <i data-lucide="folder-plus" class="w-4 h-4 text-orange-500"></i>
                    </div>
                    Nuevo Proyecto
                </h2>
            </div>
            <form method="POST" class="p-6 space-y-4">
                <input type="hidden" name="accion" value="crear_proyecto">

                <div>
                    <label for="proyecto_nombre" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Nombre del proyecto <span class="text-red-400">*</span>
                    </label>
                    <input type="text" id="proyecto_nombre" name="proyecto_nombre" required
                        class="block w-full px-4 py-2.5 border border-gray-200 rounded-lg bg-gray-50 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-orange-200 focus:border-orange-400 transition-all"
                        placeholder="Ej: Rediseño Web">
                </div>

                <div>
                    <label for="proyecto_descripcion" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Descripción
                    </label>
                    <textarea id="proyecto_descripcion" name="proyecto_descripcion" rows="3"
                        class="block w-full px-4 py-2.5 border border-gray-200 rounded-lg bg-gray-50 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-orange-200 focus:border-orange-400 transition-all resize-none"
                        placeholder="Descripción opcional del proyecto..."></textarea>
                </div>

                <button type="submit"
                    class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-orange-400 hover:bg-orange-500 text-white font-medium rounded-lg transition-all shadow-sm hover:shadow-md text-sm active:scale-[0.98]">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Crear Proyecto
                </button>
            </form>
        </div>

        <!-- ── Formulario: Nueva Tarea ── -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-white">
                <h2 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                    <div class="p-1.5 bg-blue-100 rounded-lg">
                        <i data-lucide="list-plus" class="w-4 h-4 text-blue-500"></i>
                    </div>
                    Nueva Tarea
                </h2>
            </div>
            <form method="POST" class="p-6 space-y-4">
                <input type="hidden" name="accion" value="crear_tarea">

                <div>
                    <label for="tarea_titulo" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Título <span class="text-red-400">*</span>
                    </label>
                    <input type="text" id="tarea_titulo" name="tarea_titulo" required
                        class="block w-full px-4 py-2.5 border border-gray-200 rounded-lg bg-gray-50 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-200 focus:border-blue-400 transition-all"
                        placeholder="Ej: Implementar autenticación">
                </div>

                <div>
                    <label for="tarea_descripcion" class="block text-sm font-medium text-gray-700 mb-1.5">
                        Descripción
                    </label>
                    <textarea id="tarea_descripcion" name="tarea_descripcion" rows="2"
                        class="block w-full px-4 py-2.5 border border-gray-200 rounded-lg bg-gray-50 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-200 focus:border-blue-400 transition-all resize-none"
                        placeholder="Descripción opcional..."></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="tarea_proyecto_id" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Proyecto
                        </label>
                        <select id="tarea_proyecto_id" name="tarea_proyecto_id"
                            class="block w-full px-4 py-2.5 border border-gray-200 rounded-lg bg-gray-50 text-sm text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-200 focus:border-blue-400 transition-all">
                            <option value="">Sin proyecto</option>
                            <?php foreach ($proyectos as $p): ?>
                                <option value="<?php echo $p['id']; ?>">
                                    <?php echo esc($p['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="tarea_estado" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Estado
                        </label>
                        <select id="tarea_estado" name="tarea_estado"
                            class="block w-full px-4 py-2.5 border border-gray-200 rounded-lg bg-gray-50 text-sm text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-200 focus:border-blue-400 transition-all">
                            <option value="pendiente">Pendiente</option>
                            <option value="en_progreso">En Progreso</option>
                            <option value="completada">Completada</option>
                        </select>
                    </div>

                    <div>
                        <label for="tarea_fecha" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Fecha límite
                        </label>
                        <input type="date" id="tarea_fecha" name="tarea_fecha"
                            class="block w-full px-4 py-2.5 border border-gray-200 rounded-lg bg-gray-50 text-sm text-gray-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-200 focus:border-blue-400 transition-all">
                    </div>
                </div>

                <button type="submit"
                    class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-500 hover:bg-blue-600 text-white font-medium rounded-lg transition-all shadow-sm hover:shadow-md text-sm active:scale-[0.98]">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Crear Tarea
                </button>
            </form>
        </div>

    </div>

    <!-- ═══ Tablas de listado ═══ -->
    <div class="space-y-6">

        <!-- ── Tabla: Proyectos ── -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                    <i data-lucide="folder-kanban" class="w-5 h-5 text-orange-400"></i>
                    Proyectos
                </h2>
                <span class="text-xs font-medium text-gray-400"><?php echo count($proyectos); ?> registros</span>
            </div>

            <?php if (empty($proyectos)): ?>
                <div class="p-12 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 bg-gray-50 rounded-2xl flex items-center justify-center">
                        <i data-lucide="folder-open" class="w-8 h-8 text-gray-300"></i>
                    </div>
                    <p class="text-sm text-gray-400">No hay proyectos todavía. ¡Crea el primero!</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50/80">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nombre</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Descripción</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Creado</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php foreach ($proyectos as $p): ?>
                                <tr class="hover:bg-orange-50/30 transition-colors">
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-orange-50 text-orange-600 text-xs font-bold">
                                            <?php echo $p['id']; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                        <?php echo esc($p['nombre']); ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                                        <?php echo $p['descripcion'] ? esc($p['descripcion']) : '—'; ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-400">
                                        <?php echo date('d/m/Y H:i', strtotime($p['created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm">
                                        <form method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este proyecto? Las tareas asociadas perderán su relación.');" class="inline-block">
                                            <input type="hidden" name="accion" value="eliminar_proyecto">
                                            <input type="hidden" name="proyecto_id" value="<?php echo $p['id']; ?>">
                                            <button type="submit" class="text-red-400 hover:text-red-600 transition-colors p-1.5 hover:bg-red-50 rounded-lg" title="Eliminar proyecto">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- ── Tabla: Tareas ── -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                    <i data-lucide="list-checks" class="w-5 h-5 text-blue-400"></i>
                    Tareas
                </h2>
                <span class="text-xs font-medium text-gray-400"><?php echo count($tareas); ?> registros</span>
            </div>

            <?php if (empty($tareas)): ?>
                <div class="p-12 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 bg-gray-50 rounded-2xl flex items-center justify-center">
                        <i data-lucide="clipboard-list" class="w-8 h-8 text-gray-300"></i>
                    </div>
                    <p class="text-sm text-gray-400">No hay tareas todavía. ¡Crea la primera!</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50/80">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Título</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Proyecto</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Fecha Límite</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Creado</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php foreach ($tareas as $t): ?>
                                <tr class="hover:bg-blue-50/30 transition-colors">
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-600 text-xs font-bold">
                                            <?php echo $t['id']; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900"><?php echo esc($t['titulo']); ?></p>
                                            <?php if ($t['descripcion']): ?>
                                                <p class="text-xs text-gray-400 mt-0.5 truncate max-w-xs"><?php echo esc($t['descripcion']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <?php if ($t['proyecto_nombre']): ?>
                                            <span class="inline-flex items-center gap-1">
                                                <i data-lucide="folder" class="w-3.5 h-3.5 text-orange-400"></i>
                                                <?php echo esc($t['proyecto_nombre']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-gray-300">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php
                                        $estado = $t['estado'];
                                        $badgeClass = $estadoBadge[$estado] ?? 'bg-gray-50 text-gray-600 border-gray-200';
                                        $label = $estadoLabel[$estado] ?? $estado;
                                        ?>
                                        <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full border <?php echo $badgeClass; ?>">
                                            <?php echo $label; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <?php
                                        if ($t['fecha_vencimiento']) {
                                            $fecha = strtotime($t['fecha_vencimiento']);
                                            $hoy = strtotime('today');
                                            $vencida = $fecha < $hoy && $t['estado'] !== 'completada';
                                            echo '<span class="' . ($vencida ? 'text-red-500 font-medium' : '') . '">';
                                            echo date('d/m/Y', $fecha);
                                            echo '</span>';
                                        } else {
                                            echo '<span class="text-gray-300">—</span>';
                                        }
                                        ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-400">
                                        <?php echo date('d/m/Y H:i', strtotime($t['created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm">
                                        <form method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta tarea?');" class="inline-block">
                                            <input type="hidden" name="accion" value="eliminar_tarea">
                                            <input type="hidden" name="tarea_id" value="<?php echo $t['id']; ?>">
                                            <button type="submit" class="text-red-400 hover:text-red-600 transition-colors p-1.5 hover:bg-red-50 rounded-lg" title="Eliminar tarea">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

    </div>

</div>

<!-- Estilos locales -->
<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-8px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in {
        animation: fadeIn 0.4s ease-out;
    }
</style>

<!-- Auto-ocultar mensajes tras 5 segundos -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        ['msg-exito', 'msg-error'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) {
                setTimeout(function () {
                    el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    el.style.opacity = '0';
                    el.style.transform = 'translateY(-8px)';
                    setTimeout(function () { el.remove(); }, 500);
                }, 5000);
            }
        });

        // Reinicializar iconos Lucide en contenido dinámico
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
</script>
