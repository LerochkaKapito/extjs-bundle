Ext.define("Test.TestBundle.Entity.Car", {
    extend: "Ext.data.Model",
    uses: [
        'Test.TestBundle.Entity.CarOwner',
        'Test.TestBundle.Entity.Car',
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
        },
        {
                name: "plateNumber",            
                type: "string"            
        },
        {
                name: "password",            
                type: "string"            
        },
        {
                name: "car_owner_id",            
                type: "int",            
                useNull: true,            
                mapping: "carOwner.id"            
        },
        {
                name: "related_to_id",            
                type: "int",            
                useNull: true,            
                mapping: "relatedTo.id"            
        }
    ],
    validations: [
        {
            type: "presence",
            field: "name"
        },
        {
            type: "presence",
            field: "plateNumber"
        },
        {
            type: "presence",
            field: "password"
        }
    ],
    associations: [
        {
            type: 'belongsTo',
            name: 'carOwner',
            associationKey: 'carOwner',
            foreignKey: 'car_owner_id',
            instanceName: 'carOwner',
            model: 'Test.TestBundle.Entity.CarOwner',
            getterName: 'getCarOwner',
            setterName: 'setCarOwner'
        },
        {
            type: 'belongsTo',
            name: 'relatedTo',
            associationKey: 'relatedTo',
            foreignKey: 'related_to_id',
            instanceName: 'relatedTo',
            model: 'Test.TestBundle.Entity.Car',
            getterName: 'getRelatedTo',
            setterName: 'setRelatedTo'
        },
        {
            type: 'hasMany',
            model: 'Test.TestBundle.Entity.Car',
            name: 'relatedCars',
            foreignKey: 'related_to_id'
        }
    ],
    proxy: {"type":"rest","url":"/mycars","format":"json","writer":{"type":"json","writeRecordId":false}}
});
