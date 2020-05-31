#include <unistd.h>
#include <stdlib.h>
#include <stdio.h>
#include <string.h>
#include <stdbool.h>
#include <pwd.h>
#include <sys/types.h>
#include <sys/stat.h>
#include <errno.h>

#define EXIT_SUCCESS 0
#define EXIT_FAILURE 1

struct passwd* getUser() {
    register struct passwd *pw;
    register uid_t uid;
    int c;

    uid = geteuid();
    pw = getpwuid(uid);

    if (pw) {
        return pw;
    }

    fprintf(stderr, "Cannot find username for UID %u\n", (unsigned) uid);
    exit(EXIT_FAILURE);
}

bool file_exists(char *filename) {
    struct stat buffer;
    return (stat(filename, &buffer) == 0);
}

int main(int argc, char **argv) {
    setuid(0);

    if (argc < 2) {
        printf("This binary requires at least 1 argument!");
        return EXIT_FAILURE;
    }

    char *action = strtok(argv[1], " ");

    if (strcmp(action, "test") == 0) {
        bool retVal = system("nginx -t");

        if (retVal != 0) {
            return EXIT_FAILURE;
        }

        return EXIT_SUCCESS;
    }

    if (argc < 3) {
        printf("This binary requires at least 2 arguments for that action!");
        return EXIT_FAILURE;
    }

    char *vHostFileName = strtok(argv[2], " ");
    char webserverConfigPath[] = "/etc/nginx/";
    char availablePath[] = "sites-available/";
    char snippetsPath[] = "snippets/";
    char enabledPath[] = "sites-enabled/";

    // Don't allow relative paths (should also be able to hanlde ../ paths)
    if (strstr(vHostFileName, "./") != NULL) {
        printf("'./' is not allowed for the name!");
        return EXIT_FAILURE;
    }

    // Permissions
    mode_t permissions = 0744; // Only owner can edit
    register struct passwd *user = getUser(); // Should be root user

    if (user->pw_uid != 0) {
        printf("User must be root!");
        return EXIT_FAILURE;
    }

    // Build path to sites-available
    char absoluteAvailablePath[strlen(webserverConfigPath) + strlen(availablePath) + strlen(vHostFileName)];
    strcpy(absoluteAvailablePath, webserverConfigPath);
    strcat(absoluteAvailablePath, availablePath);
    strcat(absoluteAvailablePath, vHostFileName);

    // Build path to sites-enabled
    char absoluteEnabledPath[strlen(absoluteAvailablePath)];
    strcpy(absoluteEnabledPath, webserverConfigPath);
    strcat(absoluteEnabledPath, enabledPath);
    strcat(absoluteEnabledPath, vHostFileName);

    // Build path to snippets
    char absoluteSnippetPath[strlen(webserverConfigPath) + strlen(snippetsPath) + strlen(vHostFileName)];
    strcpy(absoluteSnippetPath, webserverConfigPath);
    strcat(absoluteSnippetPath, snippetsPath);
    strcat(absoluteSnippetPath, vHostFileName);

    if (strcmp(action, "create") == 0) {
        if (argc != 4) {
            printf("%s\n", "When using the create action, you should specify 3rd parameter tmpLocation for a file to copy from.");
            return EXIT_FAILURE;
        }

        printf("Attempting to store config at '%s'\n", absoluteAvailablePath);

        char *tmpLocation = strtok(argv[3], " ");

        rename(tmpLocation, absoluteAvailablePath);
        if (!file_exists(absoluteAvailablePath)) {
            printf("%s\n", strerror(errno));
            return EXIT_FAILURE;
        }

        chown(absoluteAvailablePath, user->pw_uid, user->pw_gid);
        chmod(absoluteAvailablePath, permissions);
    } else if (strcmp(action, "create-snippet") == 0) {
        if (argc != 4) {
            printf("%s\n", "When using the create-snippet action, you should specify 3rd parameter tmpLocation for a file to copy from.");
            return EXIT_FAILURE;
        }

        printf("Attempting to store snippet at '%s'\n", absoluteSnippetPath);

        char *tmpLocation = strtok(argv[3], " ");

        rename(tmpLocation, absoluteSnippetPath);
        if (!file_exists(absoluteSnippetPath)) {
            printf("%s\n", strerror(errno));
            return EXIT_FAILURE;
        }

        chown(absoluteSnippetPath, user->pw_uid, user->pw_gid);
        chmod(absoluteSnippetPath, permissions);
    } else if (strcmp(action, "delete") == 0) {
        printf("Removing config '%s' & link '%s'\n", absoluteAvailablePath, absoluteEnabledPath);

        unlink(absoluteAvailablePath);
        unlink(absoluteEnabledPath);
    } else if (strcmp(action, "delete-snippet") == 0) {
        printf("Removing snippet '%s'\n", absoluteSnippetPath);

        unlink(absoluteSnippetPath);
    } else if (strcmp(action, "unlink") == 0) {
        printf("Removing link '%s'\n", absoluteEnabledPath);

        unlink(absoluteEnabledPath);
    } else if (strcmp(action, "link") == 0) {
        printf("Creating link '%s' -> '%s'\n", absoluteAvailablePath, absoluteEnabledPath);

        symlink(absoluteAvailablePath, absoluteEnabledPath);
    } else {
        printf("Action '%s' not available\n", action);
        return EXIT_FAILURE;
    }

    return EXIT_SUCCESS;
}
