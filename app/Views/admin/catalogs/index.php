<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>مدیریت کاتالوگ‌ها<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">لیست کاتالوگ‌ها</h3>
    </div>
    <div class="card-body">
        <table id="catalogsTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>عنوان</th>
                    <th>مالک</th>
                    <th>وضعیت</th>
                    <th>تعداد صفحات</th>
                    <th>تاریخ ایجاد</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($catalogs as $catalog): ?>
                <tr>
                    <td><?= $catalog['id'] ?></td>
                    <td><?= esc($catalog['title']) ?></td>
                    <td><?= esc($catalog['owner_name']) ?></td>
                    <td>
                        <span class="badge badge-<?= $catalog['status'] == 'completed' ? 'success' : ($catalog['status'] == 'processing' ? 'warning' : 'secondary') ?>">
                            <?= $catalog['status'] ?>
                        </span>
                    </td>
                    <td><?= $catalog['total_pages'] ?></td>
                    <td><?= $catalog['created_at'] ?></td>
                    <td>
                        <a href="/admin/catalogs/download/<?= $catalog['id'] ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-download"></i> دانلود فایل‌ها
                        </a>
                        <a href="/catalog/view/<?= $catalog['id'] ?>" target="_blank" class="btn btn-sm btn-secondary">
                            <i class="fas fa-eye"></i> مشاهده
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>

<script>
$(function () {
    $('#catalogsTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/fa.json"
        }
    });
});
</script>
<?= $this->endSection() ?>
