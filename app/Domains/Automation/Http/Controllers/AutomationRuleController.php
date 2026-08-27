<?php

namespace App\Domains\Automation\Http\Controllers;

use App\Domains\Automation\Actions\CreateAutomationRule;
use App\Domains\Automation\Actions\DeleteAutomationRule;
use App\Domains\Automation\Actions\UpdateAutomationRule;
use App\Domains\Automation\Http\Requests\StoreAutomationRuleRequest;
use App\Domains\Automation\Http\Requests\UpdateAutomationRuleRequest;
use App\Domains\Automation\Http\Resources\AutomationRuleResource;
use App\Domains\Automation\Models\AutomationRule;
use App\Support\Http\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller;

class AutomationRuleController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny', AutomationRule::class);

        return ApiResponse::collection(
            AutomationRule::paginate(request('per_page', 25))
        );
    }

    public function store(StoreAutomationRuleRequest $request, CreateAutomationRule $action)
    {
        $this->authorize('create', AutomationRule::class);

        $rule = $action->handle($request->validated(), $request->user());

        return ApiResponse::created(new AutomationRuleResource($rule));
    }

    public function show(AutomationRule $rule)
    {
        $this->authorize('view', $rule);

        return ApiResponse::ok(new AutomationRuleResource($rule));
    }

    public function update(UpdateAutomationRuleRequest $request, AutomationRule $rule, UpdateAutomationRule $action)
    {
        $this->authorize('update', $rule);

        $rule = $action->handle($rule, $request->validated(), $request->user());

        return ApiResponse::ok(new AutomationRuleResource($rule));
    }

    public function destroy(AutomationRule $rule, DeleteAutomationRule $action)
    {
        $this->authorize('delete', $rule);

        $action->handle($rule, app()->make('auth.guard')->user());

        return ApiResponse::noContent();
    }
}
