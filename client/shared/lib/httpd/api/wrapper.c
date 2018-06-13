  #include <stdlib.h>
  #include <sys/types.h>
  #include <unistd.h>

  int  main (int argc, char *argv[])
  {
  
    if (setuid(0))
    {
        perror("setuid");
        return 1;
    }
    if(setgid(0))
    {
        perror("setgid");
        return 1;
    }

     /* WARNING: Only use an absolute path to the script to execute,
      *          a malicious user might fool the binary and execute
      *          arbitary commands if not.
      * */
     return WEXITSTATUS(system ("/bin/bash /var/tmp/ogAdmClient"));
   }
