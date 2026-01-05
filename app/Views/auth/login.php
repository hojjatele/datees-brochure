<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?><?= lang('App.auth.login_title') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h4 class="mb-0"><?= lang('App.auth.login_title') ?></h4>
            </div>
            <div class="card-body">
                <form action="/login" method="post">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="email" class="form-label"><?= lang('App.auth.email') ?></label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= old('email') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label"><?= lang('App.auth.password') ?></label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary"><?= lang('App.auth.login_btn') ?></button>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center">
                <a href="/register"><?= lang('App.auth.no_account') ?></a>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
