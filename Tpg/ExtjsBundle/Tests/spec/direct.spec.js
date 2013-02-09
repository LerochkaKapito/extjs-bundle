describe('Direct Generator', function () {
    it('Test Direct Remoting exist', function() {
        expect(Test.Test).toBeDefined();
        expect(Test.Test.test).toBeDefined();
    });
    it('Execute test Remoting method', function() {
        var value = "";
        Test.Test.test(function(data) {
            value = data;
        });
        waitsFor(function() {
            return (value != "")
        }, "The Value should be set", 2000);
        runs(function() {
            expect(value['result']).toBe('test');
        });
    });
    it('Execute test Remoting method with parameter', function() {
        var value = "";
        Test.Test.test2("asd", function(data) {
            value = data;
        });
        waitsFor(function() {
            return (value != "")
        }, "The Value should be set", 2000);
        runs(function() {
            expect(value['result']).toBe('asd');
        });
    });
});