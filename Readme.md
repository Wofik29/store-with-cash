# Магазин с кассами

Представим некий абстрактный продуктовый магазин, в котором имеется несколько касс для оплаты.  
Данное приложение  имитируют работу такого магазина:
 - Есть сам магазин
 - Кассы, которые обслуживают клиента
 - Клиенты, которые что-либо покупают
 
## Установка 

Скопируйте `.env.example` в `.env`

 ## Тесты
 
 Просто запустите
 ```
./vendor/bin/phpunit --testdox
```

### Возможные улучшения
- Создать список продуктов, где каждый продукт может отбиваться разное время.
- Разное время оплаты у клиентов.
- Клиент может что-то забыть взять и побежать за этим. Это заставляет кассу стоять в ожидании
- Добавить у клиентов параметр настрояния. От него может зависить, будет ли клиент ждать очередь до конца, 
насколько бы она не была большая. Или уйдет в раздражении
- Оборудовании кассы может зависнуть: увеличит время оплаты или отбития товаров
- Возможность создавать частопокупаемые наборы продуктов: таким образом не нужно отбивать все продукты, а только сам набор. 
Это ускорит работу кассы.
- Возврат продуктов на кассе, если клиент передумал. Как одного продукта, так и всех.
- Обеденный перерыв у касс.
- Графика. Динамика изменения кол-ва клиентов на магазин и на кассы. Графики - что больше берут в магазине, если сделаны виды продуктов.