<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?><?= lang('App.dashboard.create_catalog') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><?= lang('App.dashboard.create_catalog') ?></h4>
            </div>
            <div class="card-body">
                <form id="uploadForm" enctype="multipart/form-data">
                    <?= csrf_field() ?>

                    <!-- Catalog Title -->
                    <div class="mb-3">
                        <label for="title" class="form-label"><?= lang('App.dashboard.title_column') ?></label>
                        <input type="text" class="form-control" id="title" name="title" required minlength="3" placeholder="مثال: گزارش اردوی شمال">
                    </div>

                    <!-- DOCX File -->
                    <div class="mb-3">
                        <label for="docx_file" class="form-label">فایل محتوا (Word)</label>
                        <input type="file" class="form-control" id="docx_file" name="docx_file" accept=".docx" required>
                        <div class="form-text">فقط فایل‌های .docx تا حجم 10 مگابایت</div>
                    </div>

                    <!-- Images -->
                    <div class="mb-4">
                        <label for="images" class="form-label">تصاویر (حداکثر ۲۰ تصویر)</label>
                        <input type="file" class="form-control" id="images" name="images[]" multiple accept="image/png, image/jpeg" required>
                        <div class="form-text">فایل‌های .jpg و .png تا حجم 5 مگابایت</div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="progress mb-3 d-none" id="progressContainer">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" id="progressBar" style="width: 0%">0%</div>
                    </div>

                    <!-- Alert Area -->
                    <div id="alertArea"></div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-lg btn-success" id="submitBtn">
                            <i class="bi bi-cloud-upload"></i> شروع پردازش
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.getElementById('uploadForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const form = this;
    const formData = new FormData(form);
    const progressBar = document.getElementById('progressBar');
    const progressContainer = document.getElementById('progressContainer');
    const submitBtn = document.getElementById('submitBtn');
    const alertArea = document.getElementById('alertArea');

    // Reset UI
    alertArea.innerHTML = '';
    progressContainer.classList.remove('d-none');
    progressBar.style.width = '0%';
    progressBar.textContent = '0%';
    submitBtn.disabled = true;

    const xhr = new XMLHttpRequest();
    xhr.open('POST', '/catalog/upload', true);

    // CSRF Header (if needed, but FormData usually handles input fields)
    // CodeIgniter expects valid CSRF token in post data which is included in FormData

    xhr.upload.onprogress = function(e) {
        if (e.lengthComputable) {
            const percentComplete = Math.round((e.loaded / e.total) * 100);
            progressBar.style.width = percentComplete + '%';
            progressBar.textContent = percentComplete + '%';
        }
    };

    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            progressBar.classList.add('bg-success');
            alertArea.innerHTML = `<div class="alert alert-success">آپلود موفقیت‌آمیز بود! در حال تحلیل هوشمند...</div>`;

            // Trigger AI Analysis
            const csrfName = '<?= csrf_token() ?>';
            const csrfHash = '<?= csrf_hash() ?>'; // This hash might be stale if form was submitted.
            // Actually, since we submitted a form via FormData just before (which refreshed the token if regeneration is on),
            // we should technically use the response token if returned.
            // But since memory says "CSRF token regeneration is disabled", we can reuse the initial one safely.

            const formData = new FormData();
            formData.append(csrfName, csrfHash);

            fetch('/catalog/analyze/' + response.catalog_id, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    window.location.href = '/dashboard'; // Or to review page
                } else {
                    alertArea.innerHTML = `<div class="alert alert-warning">آپلود شد اما تحلیل شکست خورد.</div>`;
                    setTimeout(() => window.location.href = '/dashboard', 2000);
                }
            })
            .catch(err => {
                console.error(err);
                 window.location.href = '/dashboard';
            });

        } else {
            let errorMsg = 'خطا در آپلود فایل.';
            try {
                const res = JSON.parse(xhr.responseText);
                if (res.errors) {
                    errorMsg = '<ul>';
                    for (const key in res.errors) {
                        errorMsg += `<li>${res.errors[key]}</li>`;
                    }
                    errorMsg += '</ul>';
                } else if (res.message) {
                    errorMsg = res.message;
                }
            } catch(e) {}

            progressBar.classList.add('bg-danger');
            alertArea.innerHTML = `<div class="alert alert-danger">${errorMsg}</div>`;
            submitBtn.disabled = false;
        }
    };

    xhr.onerror = function() {
        progressBar.classList.add('bg-danger');
        alertArea.innerHTML = `<div class="alert alert-danger">خطای شبکه رخ داد.</div>`;
        submitBtn.disabled = false;
    };

    xhr.send(formData);
});
</script>
<?= $this->endSection() ?>
