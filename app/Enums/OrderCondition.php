<?php

namespace App\Enums;

/**
 * Estados de una orden (tabla `order_condition`).
 *
 * El valor entero coincide con el `id` sembrado, por lo que puede compararse
 * directamente con la columna `condition_id` sin acoplar el código a números
 * mágicos dispersos. Mapeo: 1=generada, 2=pendiente, 3=rechazada, 4=pagada.
 */
enum OrderCondition: int
{
    case Generada = 1;   // creada, aún sin pagar
    case Pendiente = 2;  // pago en proceso (PSE/Nequi)
    case Rechazada = 3;  // declinada, anulada o con error
    case Pagada = 4;     // pago aprobado
}
