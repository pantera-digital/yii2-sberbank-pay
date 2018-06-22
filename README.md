# Yii2 Sberbank payment module

### Установка
```
composer require pantera-digital/yii2-sberbank-pay "@dev"
```
### Настройка

```
'modules' => [
    'sberbank' => [
        'class' => 'pantera\yii2\pay\sberbank\Module',
        'components' => [
            'sberbank' => [
                'class' => pantera\yii2\pay\sberbank\components\Sberbank::className(),
                'sessionTimeoutSecs' => 60 * 60 * 24 * 7,
                'login' => 'Ваш логин',
                'password' => 'Ваш пароль',
                'returnUrl' => '/sberbank/default/complete/',
            ],
        ],
        'successUrl' => '/paySuccess',
        'failUrl' => '/payFail',
    ],
]
```

### Создание заказа

Нужно пользователя перенаправить на url
```
Html::a('Оплатить', ['/sberbank/default/create', 'id' => 'Идентификатор заказа в системе'])
```