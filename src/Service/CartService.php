<?php

namespace App\Service;

use App\Entity\Livres;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{
    private const CART_SESSION_KEY = 'cart';

    public function __construct(private RequestStack $requestStack)
    {
    }

    private function getSession()
    {
        return $this->requestStack->getSession();
    }

    public function getCart(): array
    {
        return $this->getSession()->get(self::CART_SESSION_KEY, []);
    }

    public function addToCart(Livres $livre, int $quantity = 1): void
    {
        $cart = $this->getCart();
        $livreId = $livre->getId();

        if (isset($cart[$livreId])) {
            $cart[$livreId]['quantity'] += $quantity;
        } else {
            $cart[$livreId] = [
                'id' => $livreId,
                'titre' => $livre->getTitre(),
                'prix' => $livre->getPrix(),
                'image' => $livre->getImage(),
                'quantity' => $quantity,
            ];
        }

        $this->getSession()->set(self::CART_SESSION_KEY, $cart);
    }

    public function removeFromCart(int $livreId): void
    {
        $cart = $this->getCart();
        unset($cart[$livreId]);
        $this->getSession()->set(self::CART_SESSION_KEY, $cart);
    }

    public function updateQuantity(int $livreId, int $quantity): void
    {
        $cart = $this->getCart();
        if (isset($cart[$livreId])) {
            if ($quantity <= 0) {
                $this->removeFromCart($livreId);
            } else {
                $cart[$livreId]['quantity'] = $quantity;
                $this->getSession()->set(self::CART_SESSION_KEY, $cart);
            }
        }
    }

    public function clearCart(): void
    {
        $this->getSession()->remove(self::CART_SESSION_KEY);
    }

    public function getCartTotal(): float
    {
        $cart = $this->getCart();
        $total = 0;

        foreach ($cart as $item) {
            $total += $item['prix'] * $item['quantity'];
        }

        return round($total, 2);
    }

    public function getCartItemCount(): int
    {
        $cart = $this->getCart();
        $count = 0;

        foreach ($cart as $item) {
            $count += $item['quantity'];
        }

        return $count;
    }
}
