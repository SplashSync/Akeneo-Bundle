### ——————————————————————————————————————————————————————————————————
### —— Local Makefile
### ——————————————————————————————————————————————————————————————————

-include vendor/badpixxel/php-sdk/make/sdk.mk

################################################################################
# Docker Compose Containers we want to check (Akeneo 7 & Akeneo 2026)
CONTAINERS := v7 v2026
# Akeneo Install Directory inside Containers
WORKDIR := /var/www/html

COMMAND ?= echo "No command given"
COLOR_CYAN := $(shell tput setaf 6)
COLOR_RESET := $(shell tput sgr0)

.PHONY: 	help
help: 		## Show this help message
	@grep -hE '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-20s\033[0m %s\n", $$1, $$2}'

.PHONY: 	upgrade
upgrade: 	## Update Composer Packages
	composer update -q || composer update

.PHONY: 	test
test: 		## Execute Functional Tests in All Containers
	$(MAKE) up
	$(MAKE) all COMMAND="php vendor/bin/phpunit --testdox"

.PHONY: 	all
all: 		## Execute a Command in All Containers
	@$(foreach service,$(CONTAINERS), \
		set -e; \
		echo "$(COLOR_CYAN) >> Executing '$(COMMAND)' in container: $(service) $(COLOR_RESET)"; \
		$(DOCKER_COMPOSE) exec -w $(WORKDIR) $(service) bash -c "$(COMMAND)"; \
	)
