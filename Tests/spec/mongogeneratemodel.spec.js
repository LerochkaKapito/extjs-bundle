describe('Mongo Model Generator', function() {
    it('Order Model exist', function () {
        expect(Test.document.Order).toBeDefined();
    });
    it('OrderLineItem Model exist', function () {
        expect(Test.document.OrderLineItem).toBeDefined();
    });
    it('Client Model exist', function () {
        expect(Test.document.Client).toBeDefined();
    });

    describe('Model Associations', function() {
        it('order line item define in order', function() {
            var order = Ext.create('Test.document.Order');
            expect(order.lineItems).toBeDefined();
        });
        it('client define in order', function() {
            var order = Ext.create('Test.document.Order');
            expect(order.getClient).toBeDefined();
            expect(order.setClient).toBeDefined();
        });
        it('associate orders to client', function() {
            var order1 = Ext.create('Test.document.Order');
            order1.set('name', 'Invoice 1');
            order1.set('float', 4.66);
            var order2 = Ext.create('Test.document.Order');
            order2.set('name', 'Invoice 2');
            order2.set('float', 10.01);
            var person = Ext.create('Test.document.Client', {id: 10});
            person.orders().add(order1, order2);
            expect(person.orders().count()).toEqual(2);
            person.orders().remove(order2);
            expect(person.orders().count()).toEqual(1);
            expect(order1.dirty).toBeTruthy();
        })
    });
});