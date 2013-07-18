describe('Configured Model Generator', function () {
    it('Auto model should exist', function() {
        expect(Test.model.Auto).toBeDefined();
    });
    it('Car model should exist', function() {
        expect(Test.TestBundle.Entity.Car).toBeDefined();
    });
});