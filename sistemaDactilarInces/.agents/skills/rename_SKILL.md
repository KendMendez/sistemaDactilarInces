name: rename

description: Ayuda a renombrar funciones

#rename_function: 
Cambiar el nombre de los resources predeterminados por Laravel a los nombres utilizados en el proyecto 

##where or when use it:
Usar cuando se necesite cambiar el nombre de los resources predeterminados por Laravel a los nombres utilizados en el proyecto 

##how to use it:
1. Identificar los resources predeterminados por Laravel en cada controlador, ejemplo: show, index, store, update, destroy
2. Identificar los resources utilizados en el proyecto en la carpeta app/Helpers/message.php, ejemplo: stored, updated, deleted, duplicated 
3. Cambiar el nombre de los resources predeterminados por Laravel a los nombres utilizados en el proyecto usando de referencia la carpeta app/Helpers/message.php
4. Verificar que los resources estén correctamente nombrados y eliminar las funciones de nombre index 