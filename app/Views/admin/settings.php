<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>تنظیمات سیستم<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-6">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">ویرایش متغیرهای محیطی</h3>
            </div>
            <form action="/admin/settings/save" method="post">
                <div class="card-body">
                    <?= csrf_field() ?>

                    <div class="form-group">
                        <label>مدل هوش مصنوعی (تحلیل)</label>
                        <input type="text" class="form-control" name="AI_ANALYSIS_MODEL" value="<?= esc($settings['AI_ANALYSIS_MODEL']) ?>">
                        <small class="form-text text-muted">مثال: claude-3-5-sonnet-20241022</small>
                    </div>

                    <div class="form-group">
                        <label>مدل هوش مصنوعی (تصویر)</label>
                        <input type="text" class="form-control" name="AI_IMAGE_MODEL" value="<?= esc($settings['AI_IMAGE_MODEL']) ?>">
                        <small class="form-text text-muted">مثال: imagen-4.0-generate-001</small>
                    </div>

                    <div class="form-group">
                        <label>قیمت هر صفحه (تومان)</label>
                        <input type="number" class="form-control" name="PRICE_PER_PAGE" value="<?= esc($settings['PRICE_PER_PAGE']) ?>">
                    </div>

                     <div class="form-group">
                        <label>کیفیت تصویر</label>
                        <input type="text" class="form-control" name="IMAGE_QUALITY" value="<?= esc($settings['IMAGE_QUALITY']) ?>">
                    </div>

                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">ذخیره تغییرات</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
