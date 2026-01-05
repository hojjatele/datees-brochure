<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>مدیریت کاربران<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">لیست کاربران</h3>
    </div>
    <div class="card-body">
        <table id="usersTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>نام</th>
                    <th>ایمیل</th>
                    <th>موجودی</th>
                    <th>نقش</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td><?= esc($user['full_name']) ?></td>
                    <td><?= esc($user['email']) ?></td>
                    <td><?= number_format($user['wallet_balance']) ?></td>
                    <td><?= $user['role'] ?></td>
                    <td>
                        <button class="btn btn-sm btn-info btn-wallet"
                                data-id="<?= $user['id'] ?>"
                                data-name="<?= esc($user['full_name']) ?>"
                                data-balance="<?= $user['wallet_balance'] ?>"
                                data-toggle="modal" data-target="#walletModal">
                            <i class="fas fa-wallet"></i> شارژ دستی
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Wallet Modal -->
<div class="modal fade" id="walletModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">ویرایش موجودی کیف پول</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="/admin/users/updateWallet" method="post">
                <div class="modal-body">
                    <?= csrf_field() ?>
                    <input type="hidden" name="user_id" id="modalUserId">
                    <p>کاربر: <span id="modalUserName"></span></p>
                    <div class="form-group">
                        <label>موجودی جدید (تومان)</label>
                        <input type="number" name="amount" id="modalAmount" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">لغو</button>
                    <button type="submit" class="btn btn-primary">ذخیره</button>
                </div>
            </form>
        </div>
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
    $('#usersTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/fa.json"
        }
    });

    $('.btn-wallet').click(function() {
        $('#modalUserId').val($(this).data('id'));
        $('#modalUserName').text($(this).data('name'));
        $('#modalAmount').val($(this).data('balance'));
    });
});
</script>
<?= $this->endSection() ?>
