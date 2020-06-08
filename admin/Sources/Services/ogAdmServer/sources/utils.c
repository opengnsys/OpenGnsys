#include <ctype.h>
#include "utils.h"

const char *str_toupper(char *str)
{
       char *c = str;

       while (*c) {
               *c = toupper(*c);
               c++;
       }

       return str;
}
