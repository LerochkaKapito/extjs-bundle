Ext.define("Test.TestBundle.Entity.CarOwner", {
    extend: "Ext.data.Model",
    uses: [
        'Test.TestBundle.Entity.Car'
    ],
    idProperty: "id",
    fields: [
        {
                name: "id",            
                type: "int",            
                useNull: true,            
                persist: false            
        },
        {
                name: "name",            
                type: "string"            
        }
    ],
    validations: [
        {
            type: "presence",
            field: "name"
        }
    ],
    associations: [
        {
            type: 'hasMany',
            model: 'Test.TestBundle.Entity.Car',
            name: 'cars',
            foreignKey: 'car_owner_id'
        }
    ],
    proxy: {"type":"rest","url":"/mycarowners","format":"json","writer":{"type":"json","writeRecordId":false}}
});
