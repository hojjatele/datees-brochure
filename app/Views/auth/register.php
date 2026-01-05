<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?><?= lang('App.auth.register_title') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h4 class="mb-0"><?= lang('App.auth.register_title') ?></h4>
            </div>
            <div class="card-body">
                <form action="/register" method="post">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="full_name" class="form-label"><?= lang('App.auth.full_name') ?></label>
                        <input type="text" class="form-control" id="full_name" name="full_name" value="<?= old('full_name') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label"><?= lang('App.auth.email') ?></label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= old('email') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label"><?= lang('App.auth.password') ?></label>
                        <input type="password" class="form-control" id="password" name="password" required minlength="8">
                    </div>

                    <div class="mb-3">
                        <label for="password_confirm" class="form-label"><?= lang('App.auth.password_confirm') ?></label>
                        <input type="password" class="form-control" id="password_confirm" name="password_confirm" required minlength="8">
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary"><?= lang('App.auth.register_btn') ?></button>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center">
                <a href="/login"><?= lang('App.auth.have_account') ?></a>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
