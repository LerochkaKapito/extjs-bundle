describe('Model Generator', function () {
    it('Person Model exist', function () {
        expect(Test.model.Person).toBeDefined();
    });
    it('Base Book Model to exist', function() {
        expect(Test.model.BaseBook).toBeDefined();
    });
    describe('Model Fields', function () {
        var getField = (function () {
            var fields = Test.model.Person.getFields();
            var list = {};
            for (var i = 0; i < fields.length; i++) {
                list[fields[i].name] = fields[i];
            }
            return function (name) {
                return list[name];
            }
        })();
        it('contain 7 fields', function () {
            expect(Test.model.Person.getFields().length).toBe(7);
        });
        it('age field', function () {
            expect(getField('age').type).toBe(Ext.data.Types.INT);
        });
        it('active field', function () {
            expect(getField('active').type).toBe(Ext.data.Types.BOOLEAN);
        });
        it('created_at field', function () {
            expect(getField('created_at').type).toBe(Ext.data.Types.DATE);
        });
        it('email field', function () {
            expect(getField('email').type).toBe(Ext.data.Types.STRING);
        });
    });
    describe('Model Validations', function() {
        it('Presence Failed', function() {
            var p = Ext.create('Test.model.Person');
            p.set("first_name", "");
            var errors = p.validate();
            expect(errors.getByField("first_name").length).toBe(1);
        });
        it('Presence Success', function() {
            var p = Ext.create('Test.model.Person');
            p.set("last_name", "test");
            var errors = p.validate();
            expect(errors.getByField("last_name").length).toBe(0);
        });
        it('Length Failed', function() {
            var p = Ext.create('Test.model.Person');
            p.set("email", "as@ad.com");
            var errors = p.validate();
            expect(errors.getByField("email").length).toBe(1);
            p.set("email", "asdqwezxv@asdqwe.com.au");
            errors = p.validate();
            expect(errors.getByField("email").length).toBe(1);
        });
        it('Length Success', function() {
            var p = Ext.create('Test.model.Person');
            p.set("email", "as@ad.com.au");
            var errors = p.validate();
            expect(errors.getByField("email").length).toBe(0);
        });
        it('Email Failed', function() {
            var p = Ext.create('Test.model.Person');
            p.set("email", "as.ad.coma1.au");
            var errors = p.validate();
            expect(errors.getByField("email").length).toBe(1);
        });
        it('Email Success', function() {
            var p = Ext.create('Test.model.Person');
            p.set("email", "as@adas.com.au");
            var errors = p.validate();
            expect(errors.getByField("email").length).toBe(0);
        });
    });
    describe('Model Associations', function() {

    });
});