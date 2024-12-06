SHELL := /bin/bash

GIT_HASH ?= $(shell ./scripts/config.sh git_hash)
VERSION := $(shell ./scripts/config.sh opengine.version)-$(GIT_HASH)
PACKAGE_NAME := $(shell ./scripts/config.sh opengine.package)
IMAGE_NAME ?= $(shell ./scripts/config.sh opengine.image)
TAG_NAME ?= latest

TARGET_PLATFORM := $(shell ./scripts/os-type.sh)
PLATFORMS ?= $(TARGET_PLATFORM)

# BUILD_DIR := $(shell ./scripts/config.sh opengine.build_dir)
BUILD_DIR := /app/bin
# BUILD_NAME := $(shell ./scripts/config.sh opengine.build_name)
BUILD_NAME := opengine
DOCS_DIR := $(shell ./scripts/config.sh opengine.docs_dir)

INSTALLATION_NAME_OVERRIDE ?=
OPENGINE_NODESTROY := false
OPENGINE_TESTS_PREFIX ?= /
OPENGINE_TESTS_PARALLEL ?= 10
OPENGINE_TESTS_REVISION ?=

LDFLAGS=-ldflags "-X $(PACKAGE_NAME)/pkg/util.Version=$(VERSION)"

.PHONY: build
build: $(PLATFORMS)

.PHONY: build-docker
build-docker:
        # For future ci caching
        docker build \
          -f "./Dockerfile" \
          -t "${IMAGE_NAME}:${TAG_NAME}-build" \
          --target build \
          --build-arg GIT_HASH="$(GIT_HASH)" \
          --cache-from "${IMAGE_NAME}:${TAG_NAME}-build" \
          .
        docker build \
          -f "./Dockerfile" \
          -t "${IMAGE_NAME}:${TAG_NAME}" \
          --build-arg GIT_HASH="$(GIT_HASH)" \
          --cache-from "${IMAGE_NAME}:${TAG_NAME}-build" \
          --cache-from "${IMAGE_NAME}:${TAG_NAME}" \
          .

.PHONY: rebuild-docker
rebuild-docker:
        # For future ci caching
        docker build \
          -f "./Dockerfile" \
          -t "${IMAGE_NAME}-build" \
          --target build \
          --no-cache \
          .
        docker build \
          -f "./Dockerfile" \
          -t "${IMAGE_NAME}" \
          --no-cache \
          .

.PHONY: release
release: $(PLATFORMS)

.PHONY: $(PLATFORMS)
$(PLATFORMS):
        GO111MODULE=on GOOS=$@ GOARCH=amd64 CGO_ENABLED=0 go build ${LDFLAGS} -o "${BUILD_DIR}/${BUILD_NAME}-$@-amd64"
        #GO111MODULE=on GOOS=$@ GOARCH=amd64 CGO_ENABLED=0 go build -gcflags="all=-N -l" ${LDFLAGS} -o "${BUILD_DIR}/${BUILD_NAME}-$@-debug-amd64"

.PHONY: test-unit
test-unit:
        # Skip integration tests in ./tests/ using -short flag
        GO111MODULE=on go test -mod=vendor -short -tags=$(BUILDTAGS) ./...

.PHONY: test-integration
test-integration:
        cd tests \
          && export OPENGINE_CONFIRM=1 \
          && export OPENGINE_IGNORE_LOCK=1 \
          && export OPENGINE_VERBOSE=1 \
          && export OPENGINE_TIMEOUT=60 \
          && export INSTALLATION_NAME_OVERRIDE="$(INSTALLATION_NAME_OVERRIDE)" \
          && export OPENGINE_TESTS_REVISION="$(OPENGINE_TESTS_REVISION)" \
          && export BUILD_DIR=$(BUILD_DIR) \
          && export BUILD_NAME=$(BUILD_NAME)-$(TARGET_PLATFORM)-amd64 \
          && parallel --record-env \
          && bats --parallel-preserve-environment \
                  --recursive \
                  --jobs $(OPENGINE_TESTS_PARALLEL) \
                  "./integration${OPENGINE_TESTS_PREFIX}"

.PHONY: docker-run
docker-run:
        docker run --rm \
        -v "$$(pwd)/.opengine:/app/data" \
        -v "$$(pwd):/app/workdir" \
        -v "/var/run/docker.sock:/var/run/docker.sock" \
        ${IMAGE_NAME} \
        -c "opengine ${EXE_COMMAND} -c examples/v2/platform-aws/s3/manifest.yml"

.PHONY: generate-api-docs
generate-api-docs:
        SCHEMA_ROOT="./schemas" \
        NO_UPDATE_NOTIFIER=1 \
        OPENGINE_BINARY="${BUILD_DIR}/${BUILD_NAME}-$(TARGET_PLATFORM)-amd64" \
        npm run --silent schema-helper generate-api-docs "$(DOCS_DIR)/api.rst"

        for package in ./pkg/*; do \
          echo "Generating documentation for package $${package}"; \
          GO111MODULE=on godocdown "./pkg/$${package##*/}" | pandoc --from markdown --to rst -s \
            > "$(DOCS_DIR)/packages/$${package##*/}.rst" ; \
        done
	