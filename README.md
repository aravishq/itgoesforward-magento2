# itgoesforward

This module is a Magento 2 integration for the [It Goes Forward](https://itgoesforward.com) service.

## Installation

```bash
composer require aravis/itgoesforward-magento2
```

## Configuration

Go to `Stores > Configuration > Services > It Goes Forward` and configure the module.

## Usage

Since the frontend differs from project to project, this module does not provide any frontend integration.
If you're using the Hyva frontend, you can use the [itgoesforward-magento2-hyva](https://github.com/aravishq/itgoesforward-magento2-hyva) module.
When implementing the frontend, be sure to send a listing ID with the 'add to cart' request.
From there, this module will handle the rest.

In short, this is how the module works:

1. On the Product Detail Page, we fetch all the listings for the product with the endpoint `rest/V1/itgoesforward/listings/product/{product_ids}`.
2. When a listing is available for the selected configuration, we show the 'Sustainable Add to Cart' button.
3. When the button is pressed, we include the listing id in the add to cart request (`it_goes_forward`).
4. The listing id will be saved in the quote item.
5. When the order is placed, we ignore the stock deduction and send the order to the It Goes Forward API. Note that we set the qty to 0 on the order to prevent Magento stock deduction.
