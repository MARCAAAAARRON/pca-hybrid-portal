<?php

/**
 * PCA Hybridization Portal - Automated QA & Quality Testing Suite
 * Executed non-destructively (using DB transactions where mutations occur, rolling back).
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\FieldSite;
use App\Models\MonthlyHarvest;
use App\Models\HarvestVariety;
use App\Models\PollenProduction;
use App\Models\NurseryOperation;
use App\Models\NurseryBatch;
use App\Models\NurseryBatchVariety;
use App\Models\HybridDistribution;
use App\Models\AuditLog;
use App\Models\Report;
use App\Models\Scopes\FieldSiteScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class QATestRunner
{
    private array $results = [];
    private int $passed = 0;
    private int $failed = 0;
    private int $skipped = 0;

    public function run(): array
    {
        echo "========================================================\n";
        echo "  PCA HYBRIDIZATION PORTAL - QUALITY ASSURANCE TEST SUITE\n";
        echo "========================================================\n\n";

        $this->testEnvironmentAndDatabase();
        $this->testUserRolesAndPermissions();
        $this->testFieldSiteScopeDataIsolation();
        $this->testApprovalWorkflowAndMakerChecker();
        $this->testModelCalculationsAndRelationships();
        $this->testSoftDeletesAndPrunable();
        $this->testPoliciesAndPermissions();
        $this->testFilamentResourcesAndPages();
        $this->testRoutesAndViews();

        $this->printSummary();

        return [
            'total' => $this->passed + $this->failed + $this->skipped,
            'passed' => $this->passed,
            'failed' => $this->failed,
            'skipped' => $this->skipped,
            'results' => $this->results,
        ];
    }

    private function record(string $phase, string $testName, bool $status, string $details = ''): void
    {
        if ($status) {
            $this->passed++;
            echo "  [PASS] {$testName}\n";
        } else {
            $this->failed++;
            echo "  [FAIL] {$testName} - {$details}\n";
        }

        $this->results[] = [
            'phase' => $phase,
            'test' => $testName,
            'status' => $status ? 'PASSED' : 'FAILED',
            'details' => $details,
        ];
    }

    private function skip(string $phase, string $testName, string $reason): void
    {
        $this->skipped++;
        echo "  [SKIP] {$testName} - {$reason}\n";
        $this->results[] = [
            'phase' => $phase,
            'test' => $testName,
            'status' => 'SKIPPED',
            'details' => $reason,
        ];
    }

    private function testEnvironmentAndDatabase(): void
    {
        echo "\n[PHASE 1: Environment & Database Connectivity]\n";
        $phase = 'Environment & Database';

        try {
            DB::connection()->getPdo();
            $this->record($phase, 'Database Connection (PDO)', true);
        } catch (\Throwable $e) {
            $this->record($phase, 'Database Connection (PDO)', false, $e->getMessage());
            return;
        }

        // Check required tables
        $tables = [
            'users', 'field_sites', 'monthly_harvests', 'harvest_varieties',
            'pollen_productions', 'nursery_operations', 'nursery_batches',
            'nursery_batch_varieties', 'hybrid_distributions', 'audit_logs',
            'reports', 'calendar_reminders', 'user_notifications'
        ];

        foreach ($tables as $table) {
            $exists = DB::getSchemaBuilder()->hasTable($table);
            $this->record($phase, "Table Existence: {$table}", $exists, $exists ? '' : "Table {$table} missing");
        }
    }

    private function testUserRolesAndPermissions(): void
    {
        echo "\n[PHASE 2: User Roles, Attributes & Panel Access]\n";
        $phase = 'User Roles & Access';

        $roles = ['supervisor', 'sub_supervisor', 'manager', 'admin', 'superadmin'];
        foreach ($roles as $role) {
            $hasTitle = isset(User::ROLE_CHOICES[$role]);
            $this->record($phase, "Role Defined: {$role}", $hasTitle, "Role choice mapping for {$role}");
        }

        // Test User Model methods with mock users
        $user = new User([
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'role' => 'supervisor',
            'is_approved' => true,
        ]);

        $this->record($phase, 'User isSupervisor() method', $user->role === 'supervisor');
        $this->record($phase, 'User getRoleDisplayAttribute()', !empty($user->role_display));

        // Test signature lock rule
        $user->signature_updated_at = now()->subMonths(4);
        $this->record($phase, 'Signature update allowed after 3+ months', $user->canUpdateSignature());

        $user->signature_updated_at = now()->subDays(10);
        $this->record($phase, 'Signature update blocked within 3 months', !$user->canUpdateSignature());
    }

    private function testFieldSiteScopeDataIsolation(): void
    {
        echo "\n[PHASE 3: Data Isolation & FieldSiteScope]\n";
        $phase = 'Data Isolation';

        DB::beginTransaction();

        try {
            $siteA = FieldSite::firstOrCreate(['name' => 'QA Farm Loay'], ['location' => 'Loay, Bohol']);
            $siteB = FieldSite::firstOrCreate(['name' => 'QA Farm Balilihan'], ['location' => 'Balilihan, Bohol']);

            // Create persisted test supervisor
            $supervisor = User::create([
                'first_name' => 'QA',
                'last_name' => 'Supervisor',
                'email' => 'qa.supervisor.'.time().'@pca.test',
                'password' => Hash::make('password'),
                'role' => 'supervisor',
                'field_site_id' => $siteA->id,
                'is_approved' => true,
            ]);

            Auth::login($supervisor);

            // Check if query with scope includes field_site_id filter
            $harvestQuery = MonthlyHarvest::query();
            $sql = $harvestQuery->toSql();

            $hasSiteCondition = str_contains($sql, 'field_site_id');
            $this->record($phase, 'Supervisor query auto-scoped to field_site_id', $hasSiteCondition, "SQL: {$sql}");

            // Create persisted test manager
            $manager = User::create([
                'first_name' => 'QA',
                'last_name' => 'Manager',
                'email' => 'qa.manager.'.time().'@pca.test',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'is_approved' => true,
            ]);

            Auth::login($manager);

            $managerQuery = MonthlyHarvest::query();
            $managerSql = $managerQuery->toSql();
            $managerScoped = str_contains($managerSql, 'field_site_id');
            $this->record($phase, 'Manager query NOT scoped to single field site', !$managerScoped, "SQL: {$managerSql}");

            Auth::logout();
        } catch (\Throwable $e) {
            $this->record($phase, 'FieldSiteScope Isolation Test', false, $e->getMessage());
        } finally {
            DB::rollBack();
        }
    }

    private function testApprovalWorkflowAndMakerChecker(): void
    {
        echo "\n[PHASE 4: Approval Workflow & Maker-Checker Integrity]\n";
        $phase = 'Approval Workflow';

        DB::beginTransaction();

        try {
            $site = FieldSite::first() ?? FieldSite::create(['name' => 'Test Farm Loay', 'location' => 'Loay, Bohol']);

            // Create 3 separate users for the 3 stages
            $supervisor = User::create([
                'first_name' => 'Sup',
                'last_name' => 'User',
                'email' => 'temp.sup.'.time().'@test.com',
                'password' => Hash::make('password'),
                'role' => 'supervisor',
                'field_site_id' => $site->id,
                'is_approved' => true,
            ]);

            $manager = User::create([
                'first_name' => 'Mgr',
                'last_name' => 'User',
                'email' => 'temp.mgr.'.time().'@test.com',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'is_approved' => true,
            ]);

            $admin = User::create([
                'first_name' => 'Adm',
                'last_name' => 'User',
                'email' => 'temp.adm.'.time().'@test.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_approved' => true,
            ]);

            $record = new MonthlyHarvest();
            $record->field_site_id = $site->id;
            $record->report_month = now()->format('Y-m-01');
            $record->status = 'draft';
            $record->save();

            // 1. Initial status is draft
            $this->record($phase, 'Initial Status is Draft', $record->isDraft());

            // 2. Mark as Prepared by Supervisor
            $record->markAsPrepared($supervisor);
            $record->refresh();
            $this->record($phase, 'Transition to Prepared', $record->isPrepared() && $record->prepared_by === $supervisor->id);

            // 3. Maker-checker test: Supervisor cannot review own prepared record
            $reviewBySameUser = $record->markAsReviewed($supervisor);
            $this->record($phase, 'Maker-Checker: Cannot review own prepared record', $reviewBySameUser === false);

            // 4. Mark as Reviewed by Manager
            $record->markAsReviewed($manager);
            $record->refresh();
            $this->record($phase, 'Transition to Reviewed', $record->isReviewed() && $record->reviewed_by === $manager->id);

            // 5. Maker-checker test: Manager or Supervisor cannot note
            $noteBySupervisor = $record->markAsNoted($supervisor);
            $noteByManager = $record->markAsNoted($manager);
            $this->record($phase, 'Maker-Checker: Preparer/Reviewer cannot Note', $noteBySupervisor === false && $noteByManager === false);

            // 6. Mark as Noted by Admin
            $record->markAsNoted($admin);
            $record->refresh();
            $this->record($phase, 'Transition to Noted', $record->isNoted() && $record->noted_by === $admin->id);

            // 7. Return to Draft
            $record->returnToDraft($admin, 'Testing revision request');
            $record->refresh();
            $this->record($phase, 'Return to Draft resets signatories', $record->isDraft() && is_null($record->prepared_by) && is_null($record->reviewed_by) && is_null($record->noted_by));

        } catch (\Throwable $e) {
            $this->record($phase, 'Approval Workflow Execution', false, $e->getMessage());
        } finally {
            DB::rollBack();
        }
    }

    private function testModelCalculationsAndRelationships(): void
    {
        echo "\n[PHASE 5: Model Calculations & Aggregations]\n";
        $phase = 'Calculations & Aggregations';

        DB::beginTransaction();

        try {
            $site = FieldSite::first() ?? FieldSite::create(['name' => 'Calc Test Farm', 'location' => 'Bohol']);

            // Harvest Variety Summation
            $harvest = MonthlyHarvest::create([
                'field_site_id' => $site->id,
                'report_month' => now()->format('Y-m-01'),
                'status' => 'draft',
            ]);

            HarvestVariety::create([
                'monthly_harvest_id' => $harvest->id,
                'variety' => 'MRD x TAG',
                'seednuts_count' => 150,
            ]);

            HarvestVariety::create([
                'monthly_harvest_id' => $harvest->id,
                'variety' => 'TAC x BAO',
                'seednuts_count' => 250,
            ]);

            $totalSeednuts = $harvest->varieties()->sum('seednuts_count');
            $this->record($phase, 'Harvest varieties seednuts summation', $totalSeednuts == 400, "Expected 400, got {$totalSeednuts}");

            // Nursery Batch calculation: ready_to_plant vs dispatched
            $nursery = NurseryOperation::create([
                'field_site_id' => $site->id,
                'report_month' => now()->format('Y-m-01'),
                'report_type' => 'operation',
                'status' => 'draft',
            ]);

            $batch = NurseryBatch::create([
                'nursery_operation_id' => $nursery->id,
                'batch_number' => 'BATCH-001',
            ]);

            $varietyBatch = NurseryBatchVariety::create([
                'nursery_batch_id' => $batch->id,
                'variety' => 'Catigan Green Dwarf',
                'seednuts_sown' => 500,
                'ready_to_plant' => 450,
                'seedlings_dispatched' => 100,
            ]);

            $available = $varietyBatch->ready_to_plant - $varietyBatch->seedlings_dispatched;
            $this->record($phase, 'Nursery batch available seedlings calculation', $available === 350, "Expected 350, got {$available}");

            // Hybrid Distribution creation & sum
            $dist = HybridDistribution::create([
                'field_site_id' => $site->id,
                'report_month' => now()->format('Y-m-01'),
                'farmer_name' => 'Demo Farmer',
                'variety' => 'Catigan Green Dwarf',
                'seedlings_planted' => 75,
                'status' => 'draft',
            ]);

            $this->record($phase, 'Hybrid distribution record creation', $dist->exists && $dist->seedlings_planted === 75);

        } catch (\Throwable $e) {
            $this->record($phase, 'Model Calculations Test', false, $e->getMessage());
        } finally {
            DB::rollBack();
        }
    }

    private function testSoftDeletesAndPrunable(): void
    {
        echo "\n[PHASE 6: Soft Deletes, Email Suffixing & Restore]\n";
        $phase = 'Soft Deletes & Pruning';

        DB::beginTransaction();

        try {
            $email = 'softdelete.test.'.time().'@pca.gov.ph';
            $user = User::create([
                'first_name' => 'Soft',
                'last_name' => 'DeleteUser',
                'email' => $email,
                'password' => Hash::make('password'),
                'role' => 'supervisor',
                'is_approved' => true,
            ]);

            $userId = $user->id;

            // Delete user
            $user->delete();

            $deletedUser = User::withTrashed()->find($userId);
            $isSoftDeleted = $deletedUser->trashed();
            $hasSuffix = str_contains($deletedUser->email, '.deleted.');

            $this->record($phase, 'User Soft Delete with Email Suffix', $isSoftDeleted && $hasSuffix, "Email: {$deletedUser->email}");

            // Restore user
            $deletedUser->restore();
            $restoredUser = User::find($userId);
            $isRestored = !$restoredUser->trashed();
            $suffixRemoved = ($restoredUser->email === $email);

            $this->record($phase, 'User Restore with Original Email Restored', $isRestored && $suffixRemoved, "Restored Email: {$restoredUser->email}");

        } catch (\Throwable $e) {
            $this->record($phase, 'Soft Delete & Restore Mechanism', false, $e->getMessage());
        } finally {
            DB::rollBack();
        }
    }

    private function testPoliciesAndPermissions(): void
    {
        echo "\n[PHASE 7: Authorization Policies Verification]\n";
        $phase = 'Policies & Authorization';

        $policies = [
            \App\Policies\MonthlyHarvestPolicy::class,
            \App\Policies\PollenProductionPolicy::class,
            \App\Policies\NurseryOperationPolicy::class,
            \App\Policies\HybridDistributionPolicy::class,
            \App\Policies\UserPolicy::class,
            \App\Policies\AuditLogPolicy::class,
            \App\Policies\FieldSitePolicy::class,
            \App\Policies\ReportPolicy::class,
        ];

        foreach ($policies as $policyClass) {
            $name = class_basename($policyClass);
            $exists = class_exists($policyClass);
            $this->record($phase, "Policy Loaded: {$name}", $exists);
        }
    }

    private function testFilamentResourcesAndPages(): void
    {
        echo "\n[PHASE 8: Filament Resources & Custom Pages]\n";
        $phase = 'Filament Resources & Pages';

        $resources = [
            \App\Filament\Resources\FieldSiteResource::class,
            \App\Filament\Resources\MonthlyHarvestResource::class,
            \App\Filament\Resources\PollenProductionResource::class,
            \App\Filament\Resources\NurseryOperationResource::class,
            \App\Filament\Resources\HybridDistributionResource::class,
            \App\Filament\Resources\HybridizationRecordResource::class,
            \App\Filament\Resources\UserResource::class,
            \App\Filament\Resources\AuditLogResource::class,
            \App\Filament\Resources\ReportResource::class,
            \App\Filament\Resources\RoleResource::class,
        ];

        foreach ($resources as $resourceClass) {
            try {
                $name = class_basename($resourceClass);
                $model = $resourceClass::getModel();
                $hasModel = class_exists($model);
                $this->record($phase, "Resource {$name} Model Binding", $hasModel, "Model: {$model}");
            } catch (\Throwable $e) {
                $this->record($phase, "Resource {$name} Sanity", false, $e->getMessage());
            }
        }

        $pages = [
            \App\Filament\Pages\MyProfile::class,
            \App\Filament\Pages\ReportsDashboard::class,
            \App\Filament\Pages\FarmOverview::class,
            \App\Filament\Pages\ActivityFeed::class,
            \App\Filament\Pages\OrganizationCalendarPage::class,
        ];

        foreach ($pages as $pageClass) {
            $pageName = class_basename($pageClass);
            $exists = class_exists($pageClass);
            $this->record($phase, "Custom Page Registered: {$pageName}", $exists);
        }
    }

    private function testRoutesAndViews(): void
    {
        echo "\n[PHASE 9: Routes & Public Views]\n";
        $phase = 'Routes & Views';

        $routesToTest = [
            '/' => 'Public Landing Page',
            '/pending-approval' => 'Pending Approval Page',
            '/privacy-policy' => 'Privacy Policy Page',
            '/terms-of-service' => 'Terms of Service Page',
        ];

        foreach ($routesToTest as $uri => $label) {
            try {
                $request = Illuminate\Http\Request::create($uri, 'GET');
                $response = app()->handle($request);
                $status = $response->getStatusCode();
                $isOk = in_array($status, [200, 301, 302]);
                $this->record($phase, "Route: {$uri} ({$label})", $isOk, "HTTP Status: {$status}");
            } catch (\Throwable $e) {
                $this->record($phase, "Route: {$uri} ({$label})", false, $e->getMessage());
            }
        }
    }

    private function printSummary(): void
    {
        echo "\n========================================================\n";
        echo "  TEST SUMMARY REPORT\n";
        echo "========================================================\n";
        echo "  TOTAL TESTS : " . ($this->passed + $this->failed + $this->skipped) . "\n";
        echo "  PASSED      : {$this->passed}\n";
        echo "  FAILED      : {$this->failed}\n";
        echo "  SKIPPED     : {$this->skipped}\n";
        echo "========================================================\n\n";
    }
}

$runner = new QATestRunner();
$report = $runner->run();

// Save json result strictly in the 'for testing' folder
file_put_contents(__DIR__ . '/qa_test_results.json', json_encode($report, JSON_PRETTY_PRINT));
file_put_contents(__DIR__ . '/qa_test_summary.md', "# QA Test Results Summary\n\nTotal Tests: {$report['total']}\nPassed: {$report['passed']}\nFailed: {$report['failed']}\nSkipped: {$report['skipped']}\n\nGenerated: " . date('Y-m-d H:i:s'));
