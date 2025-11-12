<?php

use App\Http\Controllers\ActivityLogController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermisoController;
use App\Http\Controllers\DocenteController;
use App\Http\Controllers\CoordinadorController;
use App\Http\Controllers\MateriaController;
use App\Http\Controllers\AulaController;
use App\Http\Controllers\HorarioController;
use App\Http\Controllers\CarreraController;
use App\Http\Controllers\FacultadController;
use App\Http\Controllers\GestionController;

use App\Models\User as UserModel;



Route::prefix('auth')
    ->middleware([]) // <- esto fuerza que no tenga 'auth', 'web', ni nada
    ->withoutMiddleware(['auth', 'auth:api', 'auth:sanctum'])
    ->group(function () {
        Route::post('/login',   [AuthController::class, 'login']);
        Route::post('/logout',  [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/me',       [AuthController::class, 'me']);
    });

Route::post('/docentes', [DocenteController::class, 'store']);
Route::post('/coordinadores', [CoordinadorController::class, 'store']);

Route::middleware(['api'])->group(function () {
    Route::prefix('usuarios')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::post('/', [UserController::class, 'store']);
        Route::put('/{id}', [UserController::class, 'update']);
        Route::delete('/{id}', [UserController::class, 'destroy']);
        Route::patch('/{id}/toggle-status', [UserController::class, 'toggleStatus']);
    });

    Route::prefix('roles')->group(function () {
        Route::get('/', [RoleController::class, 'index']);
        Route::get('/{id}', [RoleController::class, 'show']);
        Route::post('/', [RoleController::class, 'store']);
        Route::put('/{id}', [RoleController::class, 'update']);
        Route::delete('/{id}', [RoleController::class, 'destroy']);
        Route::get('/{id}/permisos', [RoleController::class, 'getPermissions']);
        Route::post('/{id}/permisos', [RoleController::class, 'assignPermission']);
        Route::delete('/{id}/permisos', [RoleController::class, 'removePermission']);
    });

    Route::prefix('permisos')->group(function () {
        Route::get('/', [PermisoController::class, 'index']);
        Route::get('/{id}', [PermisoController::class, 'show']);
        Route::post('/', [PermisoController::class, 'store']);
        Route::put('/{id}', [PermisoController::class, 'update']);
        Route::delete('/{id}', [PermisoController::class, 'destroy']);
        Route::get('/{id}/roles', [PermisoController::class, 'getRoles']);
    });

    Route::prefix('docentes')->group(function () {
        Route::get('/', [DocenteController::class, 'index']);
        Route::get('/{id}', [DocenteController::class, 'show']);
        Route::put('/{id}', [DocenteController::class, 'update']);
        Route::delete('/{id}', [DocenteController::class, 'destroy']);
        Route::get('/search/especialidad', [DocenteController::class, 'searchByEspecialidad']);
    });

    

    Route::prefix('coordinadores')->group(function () {
        Route::get('/', [CoordinadorController::class, 'index']);
        Route::get('/{id}', [CoordinadorController::class, 'show']);
        Route::put('/{id}', [CoordinadorController::class, 'update']);
        Route::delete('/{id}', [CoordinadorController::class, 'destroy']);
    });

    Route::prefix('materias')->group(function () {
        Route::get('/', [MateriaController::class, 'index']);
        Route::get('/{id}', [MateriaController::class, 'show']);
        Route::post('/', [MateriaController::class, 'store']);
        Route::put('/{id}', [MateriaController::class, 'update']);
        Route::delete('/{id}', [MateriaController::class, 'destroy']);
    });

    Route::prefix('aulas')->group(function () {
        Route::get('/', [AulaController::class, 'index']);
        Route::get('/{id}', [AulaController::class, 'show']);
        Route::post('/', [AulaController::class, 'store']);
        Route::put('/{id}', [AulaController::class, 'update']);
        Route::delete('/{id}', [AulaController::class, 'destroy']);
    });

    Route::prefix('horarios')->group(function () {
        Route::get('/', [HorarioController::class, 'index']);
        Route::get('/{id}', [HorarioController::class, 'show']);
        Route::post('/', [HorarioController::class, 'store']);
        Route::put('/{id}', [HorarioController::class, 'update']);
        Route::delete('/{id}', [HorarioController::class, 'destroy']);
    });

    Route::prefix('carreras')->group(function () {
        Route::get('/', [CarreraController::class, 'index']);
        Route::get('/{id}', [CarreraController::class, 'show']);
        Route::post('/', [CarreraController::class, 'store']);
        Route::put('/{id}', [CarreraController::class, 'update']);
        Route::delete('/{id}', [CarreraController::class, 'destroy']);
    });

    Route::prefix('facultades')->group(function () {
        Route::get('/', [FacultadController::class, 'index']);
        Route::get('/{id}', [FacultadController::class, 'show']);
        Route::post('/', [FacultadController::class, 'store']);
        Route::put('/{id}', [FacultadController::class, 'update']);
        Route::delete('/{id}', [FacultadController::class, 'destroy']);
    });

    Route::prefix('gestiones')->group(function () {
        Route::get('/', [GestionController::class, 'index']);
        Route::get('/{id}', [GestionController::class, 'show']);
        Route::post('/', [GestionController::class, 'store']);
        Route::put('/{id}', [GestionController::class, 'update']);
        Route::delete('/{id}', [GestionController::class, 'destroy']);
    });
});

// 🧾 BITÁCORA
Route::prefix('bitacora')->group(function () {
    Route::get('/', [ActivityLogController::class, 'index']);
    Route::get('/{id}', [ActivityLogController::class, 'show']);
});

Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'Endpoint no encontrado'
    ], 404);
});

Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'API funcionando correctamente',
        'timestamp' => now()
    ]);
});
