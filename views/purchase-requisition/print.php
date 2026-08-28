<?php
use yii\helpers\Html;
use app\models\PurchaseRequisition;

/** @var app\models\PurchaseRequisition $model */

// Company letterhead — adjust here if the wording/address ever changes.
$companyName = 'GWI MANUFACTURING SDN BHD';
$companyReg = '(280023-X)';
$companyAddress = 'PTD 140238 Jalan Kampung Maju Jaya, Kawasan Perindustrian Kampung Maju Jaya, 81400 Johor Bahru, Johor, Malaysia';

$this->title = $model->pr_no . ' - Print';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?= Html::encode($this->title) ?></title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #000;
            margin: 0;
            padding: 20px;
        }
        .print-page {
            max-width: 1050px;
            margin: 0 auto;
            border: 2px solid #000;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td, th {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: top;
        }

        /* --- Header --- */
        .header-table td { border: none; vertical-align: middle; }
        .company-block { width: 40%; padding: 10px; }
        .company-block-inner { display: flex; align-items: center; gap: 12px; }
        .company-logo { width: 30px; height: 30px; object-fit: contain; flex-shrink: 0; }
        .company-block .name { font-weight: bold; font-size: 13px; }
        .company-block .addr { font-size: 10px; color: #333; }
        .title-block { width: 30%; text-align: center; }
        .title-block h2 { margin: 0; font-size: 18px; border: 2px solid #000; display: inline-block; padding: 6px 16px; }
        .meta-block { width: 30%; font-size: 11px; }
        .meta-block table td { border: 1px solid #000; padding: 3px 6px; }
        .meta-block table td.label { font-weight: bold; width: 45%; background: #f2f2f2; }

        /* --- Order type / customer code strip --- */
        .strip-table td { padding: 6px; font-size: 11px; }
        .checkbox { display: inline-block; width: 12px; height: 12px; border: 1px solid #000; text-align: center; line-height: 12px; margin-right: 4px; }

        /* --- Items table --- */
        .items-table th { background: #f2f2f2; font-size: 10.5px; text-align: center; }
        .items-table td { font-size: 10.5px; }
        .items-table td.num { text-align: right; }
        .items-table td.center { text-align: center; }
        .items-table tfoot td { font-weight: bold; }
        .purchasing-use-header { background: repeating-linear-gradient(45deg, #f2f2f2, #f2f2f2 4px, #e6e6e6 4px, #e6e6e6 8px); font-weight: bold; letter-spacing: 0.5px; }

        /* --- Signature blocks --- */
        .sign-table td { width: 20%; height: 90px; font-size: 10.5px; vertical-align: top; padding: 6px; }
        .sign-table .role { font-weight: bold; }
        .sign-table .sub { color: #333; font-style: italic; }
        .sign-table .name-line { margin-top: 28px; border-top: 1px dotted #000; padding-top: 2px; }

        .no-print { text-align: center; margin: 20px 0; }
        .no-print button {
            padding: 8px 20px; font-size: 14px; cursor: pointer;
        }

        @media print {
            body { padding: 0; }
            .print-page { border: 2px solid #000; max-width: 100%; }
            .no-print { display: none; }
            @page { size: A4 landscape; margin: 10mm; }
        }
    </style>
</head>
<body>

<div class="print-page">

    <table class="header-table">
        <tr>
            <td class="company-block">
                <div class="company-block-inner">
                    <img src="<?= \yii\helpers\Url::to('@web/images/logo.png') ?>" alt="Company Logo" class="company-logo" width="30" height="30">
                    <div>
                        <div class="name"><?= Html::encode($companyName) ?> <?= Html::encode($companyReg) ?></div>
                        <div class="addr"><?= Html::encode($companyAddress) ?></div>
                    </div>
                </div>
            </td>
            <td class="title-block">
                <h2>PURCHASE REQUEST FORM</h2>
            </td>
            <td class="meta-block">
                <table>
                    <tr><td class="label">PR No.</td><td><?= Html::encode($model->pr_no) ?></td></tr>
                    <tr><td class="label">Date</td><td><?= Html::encode($model->pr_date) ?></td></tr>
                    <tr><td class="label">Dept</td><td><?= Html::encode($model->department->name ?? '-') ?></td></tr>
                    <tr><td class="label">Cost Center</td><td><?= Html::encode($model->costCenter->code ?? '-') ?></td></tr>
                    <tr><td class="label">Account Code</td><td><?= Html::encode($model->account_code) ?: '&nbsp;' ?></td></tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="strip-table">
        <tr>
            <td style="width:50%;">
                <span class="checkbox"><?= $model->order_type === PurchaseRequisition::ORDER_TYPE_NEW ? '✓' : '' ?></span> New Order
            </td>
            <td style="width:50%;">
                <span class="checkbox"><?= $model->order_type === PurchaseRequisition::ORDER_TYPE_REPEAT ? '✓' : '' ?></span> Repeat Order
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width:3%;" rowspan="2">No</th>
                <th style="width:22%;" rowspan="2">Description</th>
                <th style="width:5%;" rowspan="2">Qty</th>
                <th style="width:9%;" rowspan="2">Type<br><small>(Size/Code/Colour)</small></th>
                <th style="width:8%;" rowspan="2">Cus. Code</th>
                <th style="width:15%;" rowspan="2">Purpose</th>
                <th style="width:9%;" rowspan="2">Date Needed</th>
                <th colspan="3" class="purchasing-use-header">- PURCHASING USE -</th>
            </tr>
            <tr>
                <th style="width:9%;">Unit Price</th>
                <th style="width:9%;">Total Amount</th>
                <th style="width:11%;">Quotation Ref No.</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; foreach ($model->items as $item): ?>
                <tr>
                    <td class="center"><?= $no++ ?></td>
                    <td><?= Html::encode($item->catalogItem->item_name ?? $item->description) ?>
                        <?php if ($item->specification): ?><br><small><?= Html::encode($item->specification) ?></small><?php endif; ?>
                    </td>
                    <td class="center"><?= $item->quantity ?></td>
                    <td><?= Html::encode($item->item_type) ?>&nbsp;</td>
                    <td class="center"><?= Html::encode($model->customer_code) ?: '&nbsp;' ?></td>
                    <td><?= Html::encode($item->purpose) ?>&nbsp;</td>
                    <td class="center"><?= Html::encode($item->needed_date) ?>&nbsp;</td>
                    <td class="num"><?= number_format($item->unit_price, 2) ?></td>
                    <td class="num"><?= number_format($item->total_price, 2) ?></td>
                    <td><?= Html::encode($item->quotation_ref_no) ?>&nbsp;</td>
                </tr>
            <?php endforeach; ?>
            <?php // Real form has 17 printed rows total, regardless of how many items are actually filled in. ?>
            <?php $totalFormRows = 17; $blankRows = max(0, $totalFormRows - count($model->items)); ?>
            <?php for ($i = 0; $i < $blankRows; $i++): ?>
                <tr>
                    <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                    <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                </tr>
            <?php endfor; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7" style="text-align:right;">TOTAL AMOUNT</td>
                <td>&nbsp;</td>
                <td class="num"><?= number_format($model->totalAmount, 2) ?></td>
                <td>&nbsp;</td>
            </tr>
        </tfoot>
    </table>

    <?php if ($model->purpose): ?>
        <table>
            <tr><td><strong>General Notes:</strong> <?= nl2br(Html::encode($model->purpose)) ?></td></tr>
        </table>
    <?php endif; ?>

    <table class="sign-table">
        <tr>
            <td>
                <div class="role">REQUEST BY</div>
                <div class="name-line"><?= Html::encode($model->requestedByStaff->staff_name ?? '') ?></div>
                <div>Date: <?= Html::encode($model->pr_date) ?></div>
            </td>
            <td>
                <div class="role">VERIFIED BY</div>
                <div class="sub">(Dept. Manager)</div>
                <div class="name-line"><?= Html::encode($model->verifiedByStaff->staff_name ?? '') ?></div>
                <div>Date: <?= Html::encode($model->verified_at) ?></div>
            </td>
            <td>
                <div class="role">RECEIVED BY</div>
                <div class="sub">(Purchasing)</div>
                <div class="name-line"><?= Html::encode($model->receivedByStaff->staff_name ?? '') ?></div>
                <div>Date: <?= Html::encode($model->received_at) ?></div>
            </td>
            <td>
                <div class="role">REVIEWED BY</div>
                <div class="sub">(P&L / Finance / Factory Manager)</div>
                <div class="name-line"><?= Html::encode($model->reviewedByStaff->staff_name ?? '') ?></div>
                <div>Date: <?= Html::encode($model->reviewed_at) ?></div>
            </td>
            <td>
                <div class="role">APPROVED BY</div>
                <div class="sub">(General Manager)</div>
                <div class="name-line"><?= Html::encode($model->approvedByStaff->staff_name ?? '') ?></div>
                <div>Date: <?= Html::encode($model->approved_at) ?></div>
            </td>
        </tr>
    </table>

</div>

<div class="no-print">
    <button onclick="window.print()">Print</button>
</div>

</body>
</html>