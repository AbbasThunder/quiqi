# Quiqi , A Query Builder
##Usage :
1. just extend the model class :
    ```php
    class Post extends Model
    ```
   the of table will be identified automatically *you can customise it of course using:*
   ```php
       protected $table = "posts"
    ```
   ###CRUD operations
 1. 
    just create a connection using PDO only and pass it to the constructor
    for Select :
    ```php
       $post->all()
    ```  
    You can specify the columns by passing their names in array
    ```php
    $post->all(['title','created_at'])
    ```
   for insert just use :
   ```php
    $post->create(['title' => "I'm title!"])
   ```
 and it will return the passed data. for update You can use :
 ```php
  $post->update(
    /* new Data here*
    [
        'title' => 'id' 
    ],
    // Where conditions here
    /*
    usage of where conditions here by createing an array contains alot of array (conditions):
    1. passing the column name and value
    2. passing an operator if not the equal
    3. passing a link operator (AND,OR)
     */
    [
        'column' => 'user_id',
        'operator' => '<'
        'value' => 44,
        'link_oprator' => 'and'
    ],
    [
        'column' => 'name',
        'value' => 'abbas'
    ] 
    )
```
and it will return the new data has been updated.
for delete:
    just use delete method and you can pass where conditions like the example in update
    ```php
    $post->delete(
    [ 
        'column' => 'id',
        'value' => 22
    ]
    )
    ```
    and it will return bool value
    
