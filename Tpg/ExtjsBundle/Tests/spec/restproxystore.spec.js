describe('Rest Proxy Store', function() {
    it('should save entity', function() {
        var owner = Ext.create('Test.TestBundle.Entity.CarOwner', {
            name: 'James'
        });
        var runned = false;
        runs(function() {
            owner.save({
                callback: function(record, operation, success) {
                    runned = true;
                }
            });
        });
        waitsFor(function() {
            return runned;
        });
        runs(function() {
            expect(owner.get('name')).toBe('James');
        });
    });
    it('should save existing entity association', function() {
        var owner = Ext.create('Test.TestBundle.Entity.CarOwner', {
            name: 'James'
        });
        var runned = false;
        runs(function() {
            owner.save({
                callback: function(record, operation, success) {
                    owner.cars().add({
                        name: 'Ford',
                        plateNumber: 'AA1234',
                        password: 'xx'
                    });
                    owner.cars().sync({
                        callback: function() {
                            runned = true;
                        }
                    });
                }
            });
        });
        waitsFor(function() {
            return runned;
        });
        runs(function() {
            expect(owner.cars().count()).toBe(1);
            expect(owner.cars().first().getId()).not.toBeNull();
            expect(owner.cars().first().getId()).toBeGreaterThan(0);
        });
    });
});