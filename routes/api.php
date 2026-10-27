<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MailController;
use App\Http\Controllers\GenericWorkflowInstanceController;

Route::middleware('auth:sanctum')->prefix('api')->group(function(){
    Route::get('/mail/addresses', [MailController::class, 'getMailAddresses']);
});

Route::get('/mail/accounts', [MailController::class, 'getMailAddresses']);
Route::post('/mail/incoming', [MailController::class, 'incomingMails']);
Route::post('/log-error', function(Illuminate\Http\Request $request) { 
    file_put_contents(public_path('vue_error.log'), $request->getContent() . PHP_EOL, FILE_APPEND); 
});

Route::middleware('auth:sanctum')->prefix('generic-workflow')->group(function () {
    Route::get('/tasks/{taskId}/actions', [GenericWorkflowInstanceController::class, 'getAvailableActions']);
    Route::get('/tasks/{taskId}/state', [GenericWorkflowInstanceController::class, 'getState']);
    Route::post('/tasks/{taskId}/execute', [GenericWorkflowInstanceController::class, 'executeAction']);
    Route::post('/tasks/{taskId}/pause', [GenericWorkflowInstanceController::class, 'pauseTask']);
    Route::post('/tasks/{taskId}/resume', [GenericWorkflowInstanceController::class, 'resumeTask']);
    Route::post('/tasks/{taskId}/start-sub-workflow', [GenericWorkflowInstanceController::class, 'startSubWorkflow']);
});
