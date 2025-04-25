```php
$cardInfo = new CardInfo('4111111111111111', 12, 2025, '123');
$customer = new Customer('John Doe', 'john@example.com');
$payment = new Payment(100.00, 'USD', 'Stripe')
    ->setCardInfo($cardInfo)
    ->setCustomer($customer);

$gateway = PaymentGatewayFactory::create('Stripe', $config);
$response = $gateway->charge($payment);



```
## CODEIGNITER 4 ##

```php
namespace App\Controllers;

use GatewayPayment\Core\PaymentGatewayFactory;
use GatewayPayment\Entities\Payment;
use GatewayPayment\Entities\CardInfo;
use GatewayPayment\Entities\Customer;

class PaymentController extends BaseController
{
    public function process()
    {
        $cardInfo = new CardInfo('4111111111111111', 12, 2025, '123');
        $customer = new Customer('John Doe', 'john@example.com');
        $payment = new Payment(100.00, 'USD', 'Stripe')
            ->setCardInfo($cardInfo)
            ->setCustomer($customer);

        $factory = new PaymentGatewayFactory();
        $gateway = $factory->create('Stripe');
        $response = $gateway->charge($payment);

        return $this->response->setJSON([
            'transaction_id' => $response->getTransactionId(),
            'status' => $response->getStatus(),
        ]);
    }
}

```