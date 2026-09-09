<?php

/** @var yii\web\View $this */
/** @var string $name */
/** @var string $message */
/** @var Exception $exception */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = $name;
$statusCode = $exception->statusCode ?? 500;
?>

<div class="error-page text-center py-5" style="max-width: 580px; margin: 0 auto;">
    
    <h2 class="headline font-weight-bold text-<?= $statusCode == 404 ? 'warning' : 'danger' ?>" style="font-size: 5rem;">
        <?= $statusCode ?>
    </h2>

    <div class="error-content mt-3">
        <h3 class="font-weight-bold text-dark mb-2">
            <i class="fas fa-exclamation-triangle text-<?= $statusCode == 404 ? 'warning' : 'danger' ?> mr-2"></i>
            <?= Html::encode($name) ?>
        </h3>

        <p class="text-muted mb-4 lead font-weight-500">
            <?= nl2br(Html::encode($message)) ?>
        </p>

        <div>
            <a href="<?= Url::to(['/dashboard/index']) ?>" class="btn btn-primary font-weight-bold px-4 py-2 rounded-pill shadow-xs">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Dashboard
            </a>
        </div>
    </div>

</div>