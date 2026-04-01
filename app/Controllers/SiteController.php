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
        if (!Auth::can('sites.view')) { flash('error', 'You do not have permission to access sites.'); redirect('dashboard'); }
        $siteModel = new Site();

        $this->view('dashboard/sites', [
            'pageTitle' => 'Sites & API',
            'sites' => $siteModel->allWithStats(),
            'apiEndpoint' => base_url('api/v1/inquiries/submit'),
            'csrfToken' => Csrf::token(),
            'generatedToken' => random_token(32),
            'generatedSignatureSecret' => random_token(48),
            'mappingExample' => $this->mappingExample(),
        ]);
    }

    public function create(): void
    {
        if (!Auth::can('sites.view') || !Csrf::verify($_POST['_csrf'] ?? null)) {
            flash('error', 'Invalid request token.');
            redirect('sites');
        }

        $data = $this->collectSiteFormData(true);
        if ($data === null) {
            redirect('sites');
        }

        $created = (new Site())->create($data);

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
        if (!Auth::can('sites.view')) { flash('error', 'You do not have permission to access sites.'); redirect('dashboard'); }
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
            'mappingExample' => $this->mappingExample(),
        ]);
    }

    public function update(): void
    {
        if (!Auth::can('sites.view') || !Csrf::verify($_POST['_csrf'] ?? null)) {
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
        } else {
            flash('error', 'Unable to update the site. The site key may already be used.');
        }

        redirect('sites/edit?id=' . $id);
    }

    public function rotateToken(): void
    {
        if (!Auth::can('sites.view') || !Csrf::verify($_POST['_csrf'] ?? null)) {
            flash('error', 'Invalid request token.');
            redirect('sites');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $token = trim((string) ($_POST['api_token'] ?? ''));

        if ($id <= 0 || $token === '') {
            flash('error', 'Invalid token rotation request.');
            redirect('sites');
        }

        $updated = (new Site())->rotateToken($id, $token);

        if ($updated) {
            (new InquiryLog())->create(null, Auth::id(), 'site_token_rotated', 'Rotated API token for site #' . $id);
            flash('success', 'API token rotated successfully.');
        } else {
            flash('error', 'Unable to rotate API token.');
        }

        redirect('sites/edit?id=' . $id);
    }

    public function rotateSignatureSecret(): void
    {
        if (!Auth::can('sites.view') || !Csrf::verify($_POST['_csrf'] ?? null)) {
            flash('error', 'Invalid request token.');
            redirect('sites');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $secret = trim((string) ($_POST['signature_secret'] ?? ''));

        if ($id <= 0 || $secret === '') {
            flash('error', 'Invalid signature secret rotation request.');
            redirect('sites');
        }

        $updated = (new Site())->rotateSignatureSecret($id, $secret);

        if ($updated) {
            (new InquiryLog())->create(null, Auth::id(), 'site_signature_rotated', 'Rotated signature secret for site #' . $id);
            flash('success', 'Signature secret rotated successfully.');
        } else {
            flash('error', 'Unable to rotate signature secret.');
        }

        redirect('sites/edit?id=' . $id);
    }

    private function collectSiteFormData(bool $includeSecrets): ?array
    {
        $siteName = trim((string) ($_POST['site_name'] ?? ''));
        $siteDomain = strtolower(trim((string) ($_POST['site_domain'] ?? '')));
        $siteKey = trim((string) ($_POST['site_key'] ?? ''));
        $status = trim((string) ($_POST['status'] ?? 'active'));
        $notes = trim((string) ($_POST['notes'] ?? ''));
        $requireSignature = isset($_POST['require_signature']) ? 1 : 0;
        $fieldMappingRaw = (string) ($_POST['field_mapping_json'] ?? '');
        $fieldMappingJson = $this->normalizeMappingJson($fieldMappingRaw, $mappingIsValid);

        if ($siteName === '' || $siteDomain === '' || $siteKey === '') {
            flash('error', 'Site name, domain and site key are required.');
            return null;
        }

        if (!preg_match('/^[a-z0-9][a-z0-9\-_.]*$/i', $siteKey)) {
            flash('error', 'Site key may only contain letters, numbers, dash, underscore and dot.');
            return null;
        }

        if (!$mappingIsValid) {
            flash('error', 'Field mapping JSON is invalid. Please use a JSON object.');
            return null;
        }

        if (!in_array($status, ['active', 'inactive'], true)) {
            $status = 'active';
        }

        $data = [
            'site_name' => $siteName,
            'site_domain' => preg_replace('#^https?://#i', '', $siteDomain),
            'site_key' => $siteKey,
            'require_signature' => $requireSignature,
            'status' => $status,
            'notes' => $notes !== '' ? $notes : null,
            'field_mapping_json' => $fieldMappingJson,
        ];

        if ($includeSecrets) {
            $apiToken = trim((string) ($_POST['api_token'] ?? ''));
            $signatureSecret = trim((string) ($_POST['signature_secret'] ?? ''));

            if ($apiToken === '' || $signatureSecret === '') {
                flash('error', 'API token and signature secret are required for new site creation.');
                return null;
            }

            $data['api_token'] = $apiToken;
            $data['signature_secret'] = $signatureSecret;
        }

        return $data;
    }

    private function normalizeMappingJson(string $json, ?bool &$isValid = null): ?string
    {
        $json = trim($json);

        if ($json === '') {
            $isValid = true;
            return null;
        }

        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            $isValid = false;
            return null;
        }

        $isValid = true;
        return json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    private function mappingExample(): string
    {
        return json_encode([
            'name' => ['fullname', 'your_name'],
            'email' => ['user_email', 'contact_email'],
            'title' => ['subject'],
            'content' => ['message', 'comments'],
            'from_company' => ['company', 'company_name'],
            'phone' => ['mobile', 'tel'],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
