<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use nikser\poll\AjaxSubmitButton;
use yii\helpers\Url;

?>
<style>
    .poll {
        display: inline-block;
        margin-top: 10px;
        margin-bottom: 10px;
        background: #ffffff;
    }

    .poll label {
        width: 100%;
        font-size: 10pt;
        font-weight: bold;
        display: block;
        color: #464646;
    }

    .poll label:hover {
        cursor: pointer;
    }

    .poll button[type="submit"] {
        font-weight: bold;
        font-size: 10pt;
        margin-top: 10px;
        color: #4682B4;
    }

    .poll-option-name {
        font-weight: bold;
        font-size: 10pt;
        color: #464646;
    }

    .per_container {
        font-weight: bold;
        font-size: 10pt;
        color: #464646;
        padding: 0;
        margin: 0;
        max-width: 50px;
    }

    .support_forms button[type="submit"] {
        border: none;
        font-weight: normal;
        color: #4682B4;
        margin-left: 0;
        padding: 0;
        background: #ffffff;

    }

    .support_forms button[type="submit"]:hover {
        text-decoration: underline;
    }

    .support_forms button[type="submit"]:focus {
        outline: none;
        border: none;
    }

    .support_forms {
        margin-top: 0;
    }

</style>
<div class="poll" style="width:<?= ($params['maxLineWidth'] + 55); ?>px;">
    <div
        style="max-width:<?= $params['maxLineWidth'] ?>px; word-wrap: break-word; margin-bottom: 10px; font-size:12pt; font-weight:bold;">
        <?= $pollData['poll_name'] ?>
    </div>

    <?php
    $pullName = isset($_POST['nameOfPoll']) ? $_POST['nameOfPoll'] : '';
    if (Yii::$app->user->getId()
        || ($_POST['pollStatus'] != 'show' && !$isVote)
        || ($pullName == $pollData['poll_name'] && $_POST['pollStatus'] == 'vote')
    ) {
        echo "Sign in to vote";
    }

    if ((!$isVote && Yii::$app->user->getId() && $_POST['pollStatus'] != 'show')
        || ($pullName == $pollData['poll_name'] && $_POST['pollStatus'] == 'vote' && Yii::$app->user->getId())
    ) {
        echo Html::beginForm('#', 'post', ['class' => 'uk-width-medium-1-1 uk-form uk-form-horizontal']);
        echo Html::activeRadioList($model, 'voice', $answers);
        ?>

        <input type="hidden" name="poll_name" value="<?= $pollData['poll_name'] ?>"/>

        <?php AjaxSubmitButton::widget([
            'label' => 'Vote',
            'ajaxOptions' => [
                'type' => 'POST',
                'url' => '#',
                'success' => new \yii\web\JsExpression('function(data){ $("body").html(data); }'),
            ],
            'options' => ['class' => 'customclass', 'type' => 'submit'],
        ]);
        echo Html::endForm();
    } ?>

    <?php
    if ((!$isVote && $_POST['pollStatus'] != 'show')
        || (Yii::$app->user->getId() == null && $_POST['pollStatus'] != 'show')
        || ($pullName == $pollData['poll_name'] && $_POST['pollStatus'] == 'vote' && $_POST['pollStatus'] != 'show')
    ) { ?>
        <form method="POST" action="" class="support_forms">
            <input type="hidden" name="nameOfPoll" value="<?= $pollData['poll_name'] ?>"/>
            <input type="hidden" name="pollStatus" value="show"/>
            <?php AjaxSubmitButton::widget([
                'label' => 'Show results',
                'ajaxOptions' => [
                    'type' => 'POST',
                    'url' => '#',
                    'success' => new \yii\web\JsExpression('function(data){ $("body").html(data); }'),
                ],
                'options' => ['class' => 'customclass', 'type' => 'submit'],
            ]);
            ?>
        </form>
    <?php } ?>

    <?php
    if ($isVote == true || ($pullName == $pollData['poll_name'] && $_POST['pollStatus'] == 'show')) {
        for ($i = 0; $i < count($answersData); $i++) {
            $voicesPer = empty($sumOfVoices) ? 0 : round($answersData[$i]['value'] / $sumOfVoices, 4);
            $lineWidth = $params['maxLineWidth'] * $voicesPer;
            ?>
            <div class="single-line" style="margin-bottom: 10px;">
                <div class="poll-option-name">
                    <?= $answersData[$i]['answers'] . ": " . $answersData[$i]['value'] ?>
                </div>
                <div
                    style="width: <?= $params['maxLineWidth']; ?>px;  height: <?= $params['linesHeight']; ?>px; background-color: <?= $params['backgroundLinesColor']; ?>; ">
                    <div
                        style="width: <?= $lineWidth; ?>px; height: <?= $params['linesHeight'] ?>px; background-color: <?= $params['linesColor']; ?>;">
                        <div class="per_container"
                             style="display: block; line-height:<?= $params['linesHeight'] ?>px;  height: <?= $params['linesHeight'] ?>px;
                                 position: relative; left:<?= $params['maxLineWidth'] + 5; ?>px; margin: 0;">
                            <?= ($voicesPer * 100) . "%" ?>
                        </div>
                    </div>

                </div>
            </div>
            <?php
        }
    } ?>

    <?php
    if ($isVote == false && $_POST['pollStatus'] == 'show') { ?>
        <form method="POST" action="" class="support_forms" style="margin-top: -10px;">
            <input type="hidden" name="nameOfPoll" value="<?= $pollData['poll_name'] ?>"/>
            <input type="hidden" name="pollStatus" value="vote"/>
            <?php AjaxSubmitButton::widget([
                'label' => 'Vote',
                'ajaxOptions' => [
                    'type' => 'POST',
                    'url' => '#',
                    'success' => new \yii\web\JsExpression('function(data){ $("body").html(data); }'),
                ],
                'options' => ['class' => 'customclass', 'type' => 'submit'],
            ]); ?>
        </form>
    <?php } ?>
</div>
