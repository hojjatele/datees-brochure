<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?><?= lang('App.dashboard.title') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-white bg-success mb-3">
            <div class="card-header"><?= lang('App.dashboard.wallet_balance') ?></div>
            <div class="card-body">
                <h3 class="card-title"><?= number_format($user['wallet_balance']) ?> <?= lang('App.dashboard.toman') ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-8 text-end">
        <a href="/catalog/create" class="btn btn-lg btn-primary">
            <i class="bi bi-plus-lg"></i> <?= lang('App.dashboard.create_catalog') ?>
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <?= lang('App.dashboard.my_catalogs') ?>
            </div>
            <div class="card-body">
                <?php if (empty($catalogs)): ?>
                    <p class="text-muted text-center"><?= lang('App.dashboard.no_catalogs') ?></p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th><?= lang('App.dashboard.title_column') ?></th>
                                    <th><?= lang('App.dashboard.status') ?></th>
                                    <th><?= lang('App.dashboard.pages') ?></th>
                                    <th><?= lang('App.dashboard.date') ?></th>
                                    <th><?= lang('App.dashboard.actions') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($catalogs as $catalog): ?>
                                <tr>
                                    <td><?= $catalog['id'] ?></td>
                                    <td><?= esc($catalog['title']) ?></td>
                                    <td>
                                        <?php
                                            $statusClass = 'secondary';
                                            if ($catalog['status'] === 'completed') $statusClass = 'success';
                                            elseif ($catalog['status'] === 'processing') $statusClass = 'warning';
                                            elseif ($catalog['status'] === 'failed') $statusClass = 'danger';
                                        ?>
                                        <span class="badge bg-<?= $statusClass ?>"><?= lang('App.dashboard.status_' . $catalog['status']) ?></span>
                                    </td>
                                    <td><?= $catalog['total_pages'] ?></td>
                                    <td><?= $catalog['created_at'] ?></td>
                                    <td>
                                        <a href="/catalog/view/<?= $catalog['id'] ?>" class="btn btn-sm btn-info">مشاهده</a>
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

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <?= lang('App.dashboard.recent_transactions') ?>
            </div>
            <ul class="list-group list-group-flush">
                <?php if (empty($transactions)): ?>
                    <li class="list-group-item text-muted text-center">تراکنشی یافت نشد.</li>
                <?php else: ?>
                    <?php foreach ($transactions as $t): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <small class="d-block text-muted"><?= $t['created_at'] ?></small>
                            <?= esc($t['description']) ?>
                        </div>
                        <span class="badge bg-<?= $t['type'] == 'charge' ? 'success' : 'danger' ?> rounded-pill">
                            <?= number_format($t['amount']) ?>
                        </span>
                    </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
