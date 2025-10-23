<?php
// /inc/cart_functions.php

function calcularCarrito(PDO $pdo): array {
    $cart     = $_SESSION['cart'] ?? [];
    $total    = 0.0;
    $discount = 0.0;
    $couponCode = null;

    if (!$cart) {
        return ['items'=>[], 'total'=>0, 'discount'=>0, 'grandTotal'=>0, 'coupon'=>null];
    }

    // Stocks actuales
    $ids = array_map('intval', array_keys($cart));
    $stocks = [];
    if ($ids) {
        $in  = implode(',', array_fill(0, count($ids), '?'));
        $st  = $pdo->prepare("SELECT id, stock FROM products WHERE id IN ($in)");
        $st->execute($ids);
        foreach ($st as $row) {
            $stocks[(int)$row['id']] = (int)$row['stock'];
        }
    }

    // Subtotales
    foreach ($cart as &$item) {
        $id    = (int)$item['id'];
        $price = (float)$item['price'];
        $qty   = (int)$item['qty'];

        $maxStock = $stocks[$id] ?? 0;
        $displayQty = max(1, min($qty, $maxStock > 0 ? $maxStock : 1));
        $item['displayQty'] = $displayQty;
        $item['subtotal']   = $price * $displayQty;

        $total += $item['subtotal'];
    }

    // Cupón aplicado
    if (!empty($_SESSION['applied_coupon']) && $total > 0) {
        $cinfo = $_SESSION['applied_coupon'];
        $st = $pdo->prepare("SELECT * FROM coupons WHERE id=? AND status='active' LIMIT 1");
        $st->execute([(int)$cinfo['coupon_id']]);
        if ($c = $st->fetch()) {
            $today = date('Y-m-d');
            $valid = (empty($c['start_at']) || $today >= $c['start_at'])
                  && (empty($c['end_at'])   || $today <= $c['end_at']);
            if ($valid) {
                $eligibleSubtotal = $total; // simplificado (ya tienes lógica avanzada en coupon_apply)
                $couponCode = $c['code'];
                $type   = strtolower($cinfo['type']);
                $value  = (float)$cinfo['value'];
                $cap    = isset($cinfo['max_discount']) ? (float)$cinfo['max_discount'] : null;
                $minC   = (float)($cinfo['min_cart'] ?? 0);

                if ($eligibleSubtotal > 0 && ($minC <= 0 || $total >= $minC)) {
                    if ($type === 'percent') $discount = $eligibleSubtotal * ($value/100);
                    elseif ($type === 'fixed') $discount = min($value, $eligibleSubtotal);
                    if ($cap && $cap > 0) $discount = min($discount, $cap);
                }
            }
        }
    }

    return [
        'items'      => $cart,
        'total'      => $total,
        'discount'   => $discount,
        'grandTotal' => max(0, $total - $discount),
        'coupon'     => $couponCode
    ];
}

