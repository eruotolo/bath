<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Application\Bathroom\ListBathroomsByContract;
use App\Infrastructure\Persistence\MysqliBathroomRepository;

global $link;
include ('../layouts/config.php');

// Validar entrada
if (!isset($_POST['id_Contrato']) || !is_numeric($_POST['id_Contrato'])) {
    return;
}

$idContrato = (int) $_POST['id_Contrato'];

$banosDelContrato = (new ListBathroomsByContract(new MysqliBathroomRepository($link)))->handle($idContrato);

if (empty($banosDelContrato)) {
    echo '<p class="m-0 p-2 text-center text-xs italic text-slate-400 font-sans">No hay baños asociados a este contrato.</p>';
    return;
}
?>
<label class="flex items-center gap-2 cursor-pointer">
    <input type="checkbox" id="checkTodos" class="!h-4 !w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
    <span class="font-sans text-xs font-bold uppercase tracking-wider text-slate-700">TODOS LOS BAÑOS</span>
</label>
<?php foreach ($banosDelContrato as $bano): ?>
    <label class="flex items-center gap-2 cursor-pointer" for="bath_<?php echo (int) $bano['id_Bath']; ?>">
        <input type="checkbox" name="id_Bath[]" value="<?php echo (int) $bano['id_Bath']; ?>" id="bath_<?php echo (int) $bano['id_Bath']; ?>" class="!h-4 !w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
        <span class="font-mono text-sm text-slate-700"><?php echo htmlspecialchars($bano['codigo_Bath'], ENT_QUOTES, 'UTF-8'); ?></span>
    </label>
<?php endforeach; ?>
