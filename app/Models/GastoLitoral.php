<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class GastoLitoral extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'numero_comprobante',
        'cuenta_egreso_litoral_id',
        'user_id',
        'monto',
        'fecha',
        'forma_pago',
        'descripcion',
        'comprobante_path',
        'estado'
    ];

    protected $casts = [
        'fecha' => 'date',
        'monto' => 'decimal:2',
        'deleted_at' => 'datetime',
    ];

    public const FORMAS_PAGO = [
        'efectivo' => 'Efectivo',
        'transferencia' => 'Transferencia',
        'tarjeta_credito' => 'Tarjeta de Crédito',
        'tarjeta_debito' => 'Tarjeta de Débito',
        'cheque' => 'Cheque'
    ];

    public const ESTADOS = [
        'activo' => 'Activo',
        'anulado' => 'Anulado'
    ];

    public function getMontoFormateadoAttribute(): string
    {
        return number_format($this->monto, 2, ',', '.');
    }

    public function cuentaEgresoLitoral(): BelongsTo
    {
        return $this->belongsTo(CuentaEgresoLitoral::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function generarNumeroComprobante(): string
    {
        $ultimoGasto = self::withTrashed()->latest('id')->first();
        $ultimoNumero = $ultimoGasto ? intval(substr($ultimoGasto->numero_comprobante, 4)) : 0;
        $nuevoNumero = $ultimoNumero + 1;
        return 'GAS-' . str_pad($nuevoNumero, 6, '0', STR_PAD_LEFT);
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($gasto) {
            if (empty($gasto->numero_comprobante)) {
                $gasto->numero_comprobante = self::generarNumeroComprobante();
            }
        });
    }
}
