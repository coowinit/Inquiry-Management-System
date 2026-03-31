<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\InquiryLog;
use App\Models\Site;

final class SiteController extends Controller
{
    public function index(): void
    {
        $siteModel = new Site();

        $this->view('dashboard/sites', [
            'pageTitle' => 'Sites & API',
            'sites' => $siteModel->allWithStats(),
            'apiEndpoint' => base_url('api/v1/inquiries/submit'),
            'csrfToken' => Csrf::token(),
            'generatedToken' => random_token(32),
            'generatedSignatureSecret' => random_token(48),
        ]);
    }

    public function create(): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            flash('error', 'Invalid request token.');
            redirect('sites');
        }

        $data = $this->collectSiteFormData();
        if ($data === null) {
            redirect('sites');
        }

        $siteModel = new Site();
        $created = $siteModel->create($data);

        if ($created) {
            (new InquiryLog())->create(null, Auth::id(), 'site_created', 'Created site ' . $data['site_name'] . ' (' . $data['site_key'] . ')');
            flash('success', 'Site created successfully.');
        } else {
            flash('error', 'Unable to create the site. Check whether the site key already exists.');
        }

        redirect('sites');
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $site = (new Site())->findById($id);

        if (!$site) {
            http_response_code(404);
            $this->view('errors/404', ['pageTitle' => 'Not Found'], 'layouts/guest');
            return;
        }

        $this->view('dashboard/site-edit', [
            'pageTitle' => 'Edit Site',
            'site' => $site,
            'csrfToken' => Csrf::token(),
        ]);
    }

    public function update(): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            flash('error', 'Invalid request token.');
            redirect('sites');
        }

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            flash('error', 'Invalid site id.');
            redirect('sites');
        }

        $data = $this->collectSiteFormData(false);
        if ($data === null) {
            redirect('sites/edit?id=' . $id);
        }

        $updated = (new Site())->update($id, $data);

        if ($updated) {
            (new InquiryLog())->create(null, Auth::id(), 'site_updated', 'Updated site #' . $id . ' to key ' . $data['site_key']);
            flash('success', 'Site updated successfully.');
            redirect('sites/edit?id=' . $id);
        }

        flash('error', 'Unable to update the site. The site key may already be used.');
        redirect('sites/edit?id=' . $id);
    }

    public function rotateToken(): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            flash('error', 'Invalid request token.');
            redirect('sites');
        }

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            flash('error', 'Invalid site id.');
            redirect('sites');
        }

        $token = random_token(32);
        (new Site())->rotateApiToken($id, $token);
        (new InquiryLog())->create(null, Auth::id(), 'site_token_rotated', 'Rotated API token for site #' . $id);
        flash('success', 'API token rotated successfully.');
        redirect('sites/edit?id=' . $id);
    }

    public function rotateSignatureSecret(): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            flash('error', 'Invalid request token.');
            redirect('sites');
        }

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            flash('error', 'Invalid site id.');
            redirect('sites');
        }

        $secret = random_token(48);
        (new Site())->rotateSignatureSecret($id, $secret);
        (new InquiryLog())->create(null, Auth::id(), 'site_signature_rotated', 'Rotated signature secret for site #' . $id);
        flash('success', 'Signature secret rotated successfully.');
        redirect('sites/edit?id=' . $id);
    }

    private function collectSiteFormData(bool $includeSecrets = true): ?array
    {
        $siteName = trim((string) ($_POST['site_name'] ?? ''));
        $siteDomain = strtolower(trim((string) ($_POST['site_domain'] ?? '')));
        $siteKey = trim((string) ($_POST['site_key'] ?? ''));
        $status = trim((string) ($_POST['status'] ?? 'active'));
        $notes = trim((string) ($_POST['notes'] ?? ''));
        $requireSignature = isset($_POST['require_signature']) ? 1 : 0;

        if ($siteName === '' || $siteDomain === '' || $siteKey === '') {
            flash('error', 'Site name, domain and site key are required.');
            return null;
        }

        if (!preg_match('/^[a-z0-9][a-z0-9_-]{2,79}$/', $siteKey)) {
            flash('error', 'Site key can only contain lowercase letters, numbers, underscore and hyphen.');
            return null;
        }

        if (!preg_match('/^[a-z0-9.-]+$/', $siteDomain)) {
            flash('error', 'Please enter a valid domain, for example a.com or www.a.com.');
            return null;
        }

        if (!in_array($status, ['active', 'inactive'], true)) {
            flash('error', 'Invalid site status.');
            return null;
        }

        $data = [
            'site_name' => $siteName,
            'site_domain' => $siteDomain,
            'site_key' => $siteKey,
            'require_signature' => $requireSignature,
            'status' => $status,
            'notes' => $notes !== '' ? $notes : null,
        ];

        if ($includeSecrets) {
            $data['api_token'] = trim((string) ($_POST['api_token'] ?? random_token(32)));
            $data['signature_secret'] = trim((string) ($_POST['signature_secret'] ?? random_token(48)));

            if ($data['api_token'] === '' || $data['signature_secret'] === '') {
                flash('error', 'API token and signature secret are required.');
                return null;
            }
        }

        return $data;
    }
}
