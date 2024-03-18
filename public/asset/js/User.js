const User = {
    delete: function(id, name) {
        let resp = confirm('Do you want to delete the user ' + name + '?');
        if (resp) {
            window.location.href = '/user/delete/' + id;
        }
    }
};
