<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>شارژ کیف پول<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0">شارژ کیف پول</h4>
            </div>
            <div class="card-body">
                <form action="/payment/process" method="post">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="amount" class="form-label">مبلغ شارژ (تومان)</label>
                        <input type="number" class="form-control form-control-lg" id="amount" name="amount" min="1000" step="1000" required>
                        <div class="form-text">حداقل مبلغ: ۱,۰۰۰ تومان</div>
                    </div>

                    <div class="alert alert-info">
                        موجودی فعلی شما: <strong><?= number_format(session()->get('userData')['wallet_balance'] ?? 0) ?> تومان</strong>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">پرداخت و افزایش موجودی</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
