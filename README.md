#api end-points:
* \*/register
* \*/login
* \*/search
* \*/user/{userId}/add-friend/{friendId}
* \*/user/{userId}/remove-friend/{friendId}


**register**  
_method_: POST  
_takes_: JSON  
_returns_: JSON (user data)  
_data structure_:  
```
{
    "email": string,
    "name": string,
    "lastName": string,
    "plainPassword": string  
 }
```  
**login**  
_method_: POST  
_takes_: JSON  
_returns_: JSON (user data)  
_data structure_:  
```
{
    "email": string,
    "password": string  
 }
```  
**search**  
_method_: GET  
_takes_: query-string  
_returns_: JSON (array of user data)  
_data structure_: (all optional parameters) 
```
{
    "email": string,
    "name": string,
    "lastName": string,
    "user": integer  
 }
```  
**add-friend**  
_method_: GET    
_returns_: JSON (user data)  
_url pattern_:  
```
{
    /user/{user_adding}/add-friend/{user_added}  
 }
```
 

**remove-friend**  
_method_: GET    
_returns_: JSON (user data)  
_url pattern_:  
      
```
{
    /user/{user_adding}/remove-friend/{user_added}  
 }
```
     