describe('Rest Proxy in Model', function() {
    it('should save entity', function() {
        var owner = Ext.create('Test.TestBundle.Entity.CarOwner', {
            name: 'James'
        });
        var runned = false;
        runs(function() {
            owner.save({
                callback: function(record, operation, success) {
                    Test.TestBundle.Entity.CarOwner.load(record.getId(), {
                        callback: function(record) {
                            owner = record;
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
            expect(owner.get('name')).toBe('James');
        });
    });
    it('should save existing entity with new hasMany association', function() {
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
                    }, {
                        name: 'BMW',
                        plateNumber: 'ASF87654',
                        password: 'xx'
                    });
                    owner.cars().sync({
                        callback: function() {
                            Test.TestBundle.Entity.CarOwner.load(owner.getId(), {
                                callback: function(record) {
                                    owner = record;
                                    runned = true;
                                }
                            });
                        }
                    });
                }
            });
        });
        waitsFor(function() {
            return runned;
        });
        runs(function() {
            expect(owner).not.toBeNull();
            expect(owner.cars().count()).toBe(2);
            expect(owner.cars().first().get('name')).toBe('Ford');
            expect(owner.cars().first().getCarOwner()).toBe(owner);
        });
    });
    it('should save existing entity with existing hasMany association', function() {
        var owner = Ext.create('Test.TestBundle.Entity.CarOwner', {
            name: 'James'
        });
        var runned = false;
        runs(function() {
            owner.save({
                callback: function(record, operation, success) {
                    var car = Ext.create('Test.TestBundle.Entity.Car', {
                        name: 'Ford',
                        plateNumber: 'AA1234',
                        password: 'xx'
                    });
                    car.save({
                        callback: function(record) {
                            owner.cars().add(record);
                            owner.cars().sync({
                                callback: function() {
                                    owner.cars().first().getCarOwner({
                                        callback: function() {
                                            runned = true;
                                        }
                                    });
                                }
                            });
                        }
                    });
                }
            });
        });
        waitsFor(function() {
            return runned;
        });
        runs(function() {
            expect(owner).not.toBeNull();
            expect(owner.cars().count()).toBe(1);
            expect(owner.cars().first().get('name')).toBe('Ford');
            expect(owner.cars().first().getCarOwner().getData()).toEqual(owner.getData());
        });
    });
    it('should save existing entity with existing belongTo association', function() {
        var car = Ext.create('Test.TestBundle.Entity.Car', {
            name: 'Ford',
            plateNumber: 'AA1234',
            password: 'xx'
        });
        var runned = false;
        runs(function() {
            car.save({
                callback: function(record) {
                    var owner = Ext.create('Test.TestBundle.Entity.CarOwner', {
                        name: 'James'
                    });
                    owner.save({
                        callback: function(record) {
                            car.setCarOwner(record, {
                                callback: function() {
                                    runned = true;
                                }
                            });
                        }
                    });
                }
            });
        });
        waitsFor(function() {
            return runned;
        });
        runs(function() {
            expect(car.getCarOwner()).not.toBeNull();
            expect(car.getCarOwner().getId()).toBeGreaterThan(0);
        });
    });
    it('should update entity', function() {
        var car = Ext.create('Test.TestBundle.Entity.Car', {
            name: 'Ford',
            plateNumber: 'AA1234',
            password: 'xx'
        });
        var originalId;
        var runned = false;
        runs(function() {
            car.save({
                callback: function(record) {
                    originalId = record.getId();
                    record.set('name', 'BMW');
                    record.save({
                        callback: function(record) {
                            car = record;
                            runned = true;
                        }
                    })
                }
            })
        });
        waitsFor(function() {
            return runned;
        });
        runs(function() {
            expect(originalId).toBe(car.getId());
            expect(car.get('name')).toBe('BMW');
        });
    })
});
describe('Rest Proxy in Store', function() {
    var store = Ext.create('Ext.data.Store', {
        model: 'Test.TestBundle.Entity.Car',
        autoLoad: false,
        proxy: {
            type: 'rest',
            url: '/mycars',
            format: 'json'
        }
    });
    it('should load all the entity', function() {
        var runned = false;
        runs(function() {
            var car = Ext.create('Test.TestBundle.Entity.Car', {
                name: 'Ford',
                plateNumber: 'AA1234',
                password: 'xx'
            });
            car.save({
                callback: function() {
                    store.load({
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
            expect(store.count()).toBeGreaterThan(0);
        })
    });
    it('should remove all entity', function () {
        var runned = false;
        runs(function () {
            store.load({
                callback: function() {
                    store.removeAll();
                    store.sync({
                        callback: function() {
                            store.load({
                                callback: function() {
                                    runned = true;
                                }
                            })
                        }
                    })
                }
            });
        });
        waitsFor(function () {
            return runned;
        });
        runs(function () {
            expect(store.count()).toBe(0);
        });
    });
    it('should add new entity', function () {
        var runned = false;
        runs(function () {
            store.add([
                {
                    name: 'Ford',
                    plateNumber: 'AA1234',
                    password: 'xx'
                },
                {
                    name: 'BMW',
                    plateNumber: 'ZZ54',
                    password: 'xx'
                }
            ]);
            store.sync({
                callback: function() {
                    runned = true;
                }
            });
        });
        waitsFor(function () {
            return runned;
        });
        runs(function () {
            expect(store.count()).toBe(2);
            expect(store.getNewRecords().length).toBe(0);
        });
    });
});