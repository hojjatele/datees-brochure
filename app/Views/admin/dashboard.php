<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>داشبورد<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-lg-4 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3><?= $stats['total_users'] ?></h3>
                <p>کاربران</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
            <a href="/admin/users" class="small-box-footer">اطلاعات بیشتر <i class="fas fa-arrow-circle-left"></i></a>
        </div>
    </div>
    <div class="col-lg-4 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3><?= $stats['total_catalogs'] ?></h3>
                <p>کاتالوگ‌ها</p>
            </div>
            <div class="icon">
                <i class="fas fa-book"></i>
            </div>
            <a href="/admin/catalogs" class="small-box-footer">اطلاعات بیشتر <i class="fas fa-arrow-circle-left"></i></a>
        </div>
    </div>
    <div class="col-lg-4 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3><?= number_format($stats['total_revenue']) ?></h3>
                <p>درآمد کل (تومان)</p>
            </div>
            <div class="icon">
                <i class="fas fa-coins"></i>
            </div>
            <a href="#" class="small-box-footer">اطلاعات بیشتر <i class="fas fa-arrow-circle-left"></i></a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">نمودار درآمد (۷ روز اخیر)</h3>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($chartData['labels']) ?>,
            datasets: [{
                label: 'درآمد (تومان)',
                data: <?= json_encode($chartData['data']) ?>,
                borderColor: '#28a745',
                tension: 0.1
            }]
        }
    });
</script>
<?= $this->endSection() ?>
