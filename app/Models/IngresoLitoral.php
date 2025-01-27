<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class IngresoLitoral extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array
     */
    protected $fillable = [
        'numero_comprobante',
        'cuenta_ingreso_litoral_id',
        'user_id',
        'monto',
        'fecha',
        'forma_pago',
        'descripcion',
        'comprobante_path',
        'estado'
    ];

    /**
     * Los atributos que deben ser convertidos.
     *
     * @var array
     */
    protected $casts = [
        'fecha' => 'date',
        'monto' => 'decimal:2',
        'deleted_at' => 'datetime',
    ];

    /**
     * Los atributos que deben ser ocultos para las matrices.
     *
     * @var array
     */
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * Las opciones disponibles para forma de pago.
     *
     * @var array
     */
    public const FORMAS_PAGO = [
        'efectivo' => 'Efectivo',
        'transferencia' => 'Transferencia',
        'tarjeta_credito' => 'Tarjeta de Crédito',
        'tarjeta_debito' => 'Tarjeta de Débito',
        'cheque' => 'Cheque'
    ];

    /**
     * Las opciones disponibles para estado.
     *
     * @var array
     */
    public const ESTADOS = [
        'activo' => 'Activo',
        'anulado' => 'Anulado'
    ];

    /**
     * Obtiene el monto formateado en formato colombiano.
     *
     * @return string
     */
    public function getMontoFormateadoAttribute(): string
    {
        return number_format($this->monto, 2, ',', '.');
    }

    /**
     * Relación con la cuenta de ingreso litoral.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cuentaIngresoLitoral(): BelongsTo
    {
        return $this->belongsTo(CuentaIngresoLitoral::class);
    }

    /**
     * Relación con el usuario.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Genera un número de comprobante único.
     *
     * @return string
     */
    public static function generarNumeroComprobante(): string
    {
        $ultimoIngreso = self::withTrashed()->latest('id')->first();
        $ultimoNumero = $ultimoIngreso ? intval(substr($ultimoIngreso->numero_comprobante, 4)) : 0;
        $nuevoNumero = $ultimoNumero + 1;
        return 'ING-' . str_pad($nuevoNumero, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Scope para filtrar por estado.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $estado
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    /**
     * Scope para filtrar por fecha.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $fechaInicio
     * @param string $fechaFin
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFechaBetween($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
    }

    /**
     * Verifica si el ingreso puede ser anulado.
     *
     * @return bool
     */
    public function puedeSerAnulado(): bool
    {
        return $this->estado === 'activo';
    }

    /**
     * Boot del modelo.
     */
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($ingreso) {
            if (empty($ingreso->numero_comprobante)) {
                $ingreso->numero_comprobante = self::generarNumeroComprobante();
            }
        });
    }
}
