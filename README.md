
# Ticket system

Helpdesk ticket system for all company departments.


## Installation



```bash
php artisan migrate

#Import LDAP users
php artisan ldap:import users --filter "(department=*)" --attributes "cn,samaccountname,mail,title,departme
nt,distinguishedname,extensionattribute11,manager,mailnickname,mobile,pager"


php artisan app:install
```
    
