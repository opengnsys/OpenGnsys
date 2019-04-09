export class GlobunetFormType {
  static getForm(object: Object) {
    const form = [];
    Object.keys(object).forEach(function (value, index, array) {
      // Excluir el id
      if (value !== 'id') {
        const field = {field: '', type: '', name: '', label: '', style: ''};
        field.field = value;
        field.name = value;
        field.label = value;
        field.type = typeof object[value];
        if (typeof object[value] === 'string') {
          field.type = 'text';
        } else if (typeof object[value] === 'boolean') {
          field.type = 'checkbox';
        } else if (Array.isArray(object[value])) {
          field.type = 'select';
        }
        form.push(field);
      }
    });
    return form;
  }

  setFieldType(form, fieldName, type) {
    const index = form.findIndex((field) => field.field === fieldName);
    if (index !== -1) {
      form[index].type = type;
    }
  }

  getField(form, fieldName) {
    let result = null;
    const index = form.findIndex((field) => field.field === fieldName);
    if (index !== -1) {
      result = form[index];
    }
    return result;
  }

  removeField(form, fieldName) {
    const index = form.findIndex((field) => field.field === fieldName);
    if (index !== -1) {
      form.splice(index, 1);
    }
    return form;
  }
}
