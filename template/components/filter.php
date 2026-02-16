<?php

use Photobooth\Enum\ImageFilterEnum;
use Photobooth\Service\LanguageService;
use Photobooth\Utility\ImageUtility;

$languageService = LanguageService::getInstance();

$defaultFilter = $config['filters']['defaults'] ?? ImageFilterEnum::PLAIN;
$defaultStr = $defaultFilter instanceof ImageFilterEnum ? $defaultFilter->value : (string) $defaultFilter;
$disabledRaw = $config['filters']['disabled'] ?? [];
$disabledStrings = array_map(static function ($d) {
    return $d instanceof ImageFilterEnum ? $d->value : (string) $d;
}, $disabledRaw);

$cubeFilters = ImageUtility::getCubeFilters();

?>
<div id="filternav" class="sidenav dragscroll rotarygroup">
    <button
        type="button"
        class="sidenav-close rotaryfocus"
        data-command="sidenav-close"
        title="<?=$languageService->translate('close')?>"
        >
        <i class="<?php echo $config['icons']['close']; ?>"></i>
    </button>
    <div class="sidenav-list">
        <?php foreach (ImageFilterEnum::cases() as $filter): ?>
            <?php if (!in_array($filter->value, $disabledStrings, true)): ?>
                <button
                    type="button"
                    class="sidenav-list-item<?php echo $defaultStr === $filter->value ? ' sidenav-list-item--active' : ''; ?> rotaryfocus"
                    data-filter="<?= htmlspecialchars($filter->value) ?>"
                >
                    <?= htmlspecialchars($filter->label()) ?>
                </button>
            <?php endif; ?>
        <?php endforeach; ?>
        <?php foreach ($cubeFilters as $cube): ?>
            <?php if (!in_array($cube['value'], $disabledStrings, true)): ?>
                <button
                    type="button"
                    class="sidenav-list-item<?php echo $defaultStr === $cube['value'] ? ' sidenav-list-item--active' : ''; ?> rotaryfocus"
                    data-filter="<?= htmlspecialchars($cube['value']) ?>"
                >
                    <?= htmlspecialchars($cube['label']) ?> <span class="sidenav-list-item-badge">LUT</span>
                </button>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>
