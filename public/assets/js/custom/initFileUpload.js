/**
 * Переиспользуемый компонент для управления загрузкой файлов через FilePond
 */
class FileUploadManager {
    constructor(options = {}) {
        this.config = {
            pondSelector: '.my-pond',
            submitBtnSelector: null,
            formSelector: null,
            modalSelector: null,
            tempFolderInputSelector: '#hidden_temp_folder',

            uploadRoute: '/cabinet/files/upload',
            deleteRoute: '/cabinet/files/delete',
            deleteTempFolderRoute: '/cabinet/files/delete-temp-folder',

            csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',

            allowMultiple: true,
            acceptedFileTypes: [
                'image/png',
                'image/jpg',
                'image/jpeg',
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ],
            maxFileSize: '10MB',

            labelFileTypeNotAllowed: 'Неподдерживаемый формат файла',
            labelMaxFileSizeExceeded: 'Файл превышает максимальный размер',
            labelIdle: 'Перетащите файлы сюда или нажмите для выбора',

            requireFilesForSubmit: false,
            autoGenerateTempFolder: true,
            cleanupOnModalClose: true,

            onFileAdd: null,
            onFileRemove: null,
            onUploadSuccess: null,
            onUploadError: null,
            onDeleteSuccess: null,
            onDeleteError: null,

            ...options
        };

        this.tempFolder = null;
        this.uploadedFiles = [];
        this.pondInstance = null;
        this.submitBtn = null;
        this.modal = null;
        this.form = null;
        this.activeUploads = 0;

        this.init();
    }

    init() {
        this.initElements();
        this.initFilePond();
        this.bindEvents();

        if (this.config.autoGenerateTempFolder && !this.modal) {
            this.generateTempFolder();
        }
    }

    initElements() {
        if (this.config.submitBtnSelector) {
            this.submitBtn = $(this.config.submitBtnSelector);
        }

        if (this.config.formSelector) {
            this.form = $(this.config.formSelector);
        }

        if (this.config.modalSelector) {
            this.modal = $(this.config.modalSelector);
        }
    }

    generateTempFolder() {
        this.tempFolder = window.crypto.randomUUID();
        const tempFolderInput = $(this.config.tempFolderInputSelector);
        if (tempFolderInput.length) {
            tempFolderInput.val(this.tempFolder);
        }
        this.uploadedFiles.length = 0;
        this.updateSubmitButton();
    }

    initFilePond() {
        if (typeof FilePondPluginFileValidateType !== 'undefined') {
            FilePond.registerPlugin(FilePondPluginFileValidateType);
        }
        if (typeof FilePondPluginFileValidateSize !== 'undefined') {
            FilePond.registerPlugin(FilePondPluginFileValidateSize);
        }

        const pondElement = document.querySelector(this.config.pondSelector);
        if (!pondElement) {
            console.error('FilePond element not found:', this.config.pondSelector);
            return;
        }

        this.pondInstance = FilePond.create(pondElement, {
            server: {
                process: this.processFile.bind(this),
                revert: this.revertFile.bind(this)
            },
            allowMultiple: this.config.allowMultiple,
            acceptedFileTypes: this.config.acceptedFileTypes,
            labelFileTypeNotAllowed: this.config.labelFileTypeNotAllowed,
            maxFileSize: this.config.maxFileSize,
            labelMaxFileSizeExceeded: this.config.labelMaxFileSizeExceeded,
            labelIdle: this.config.labelIdle
        });

        this.pondInstance.on('addfile', (error, file) => {
            if (!error) {
                this.updateSubmitButton();
                if (this.config.onFileAdd) {
                    this.config.onFileAdd(file, this.uploadedFiles);
                }
            }
        });

        this.pondInstance.on('removefile', (error, file) => {
            if (!error) {
                if (file.serverId) {
                    const index = this.uploadedFiles.indexOf(file.serverId);
                    if (index > -1) {
                        this.uploadedFiles.splice(index, 1);
                    }
                }
                this.updateSubmitButton();
                if (this.config.onFileRemove) {
                    this.config.onFileRemove(file, this.uploadedFiles);
                }
            }
        });

        this.pondInstance.on('processfilestart', () => {
            this.activeUploads++;
            this.updateSubmitButton();
        });

        this.pondInstance.on('processfile', () => {
            this.activeUploads = Math.max(0, this.activeUploads - 1);
            this.updateSubmitButton();
        });

        this.pondInstance.on('processfileabort', () => {
            this.activeUploads = Math.max(0, this.activeUploads - 1);
            this.updateSubmitButton();
        });

        this.pondInstance.on('processfileerror', () => {
            this.activeUploads = Math.max(0, this.activeUploads - 1);
            this.updateSubmitButton();
        });
    }

    processFile(fieldName, file, metadata, load, error, progress, abort) {
        if (!this.tempFolder) {
            this.generateTempFolder();
        }

        const formData = new FormData();
        formData.append('media', file);
        formData.append('folder', this.tempFolder);

        const xhr = new XMLHttpRequest();
        xhr.open('POST', this.config.uploadRoute);
        xhr.setRequestHeader('X-CSRF-TOKEN', this.config.csrfToken);

        xhr.upload.onprogress = (e) => {
            progress(e.lengthComputable, e.loaded, e.total);
        };

        xhr.onload = () => {
            if (xhr.status >= 200 && xhr.status < 300) {
                const response = JSON.parse(xhr.responseText);
                this.uploadedFiles.push(response.path);
                load(response.path);
                this.updateSubmitButton();
                if (this.config.onUploadSuccess) {
                    this.config.onUploadSuccess(response, this.uploadedFiles);
                }
            } else {
                const errorMsg = 'Ошибка загрузки';
                error(errorMsg);
                if (this.config.onUploadError) {
                    this.config.onUploadError(errorMsg, xhr);
                }
            }
        };

        xhr.onerror = () => {
            const errorMsg = 'Ошибка сети при загрузке';
            error(errorMsg);
            if (this.config.onUploadError) {
                this.config.onUploadError(errorMsg, xhr);
            }
        };

        xhr.send(formData);

        return {
            abort: () => {
                xhr.abort();
                abort();
            }
        };
    }

    revertFile(uniqueFileId, load, error) {
        $.ajax({
            url: this.config.deleteRoute,
            type: 'DELETE',
            contentType: 'application/json',
            data: JSON.stringify([uniqueFileId]),
            headers: {
                'X-CSRF-TOKEN': this.config.csrfToken
            },
            success: () => {
                const index = this.uploadedFiles.indexOf(uniqueFileId);
                if (index > -1) {
                    this.uploadedFiles.splice(index, 1);
                }
                load();
                this.updateSubmitButton();
                if (this.config.onDeleteSuccess) {
                    this.config.onDeleteSuccess(uniqueFileId, this.uploadedFiles);
                }
            },
            error: (xhr) => {
                const errorMsg = 'Ошибка удаления';
                console.error('Delete error:', errorMsg);
                error(errorMsg);
                if (this.config.onDeleteError) {
                    this.config.onDeleteError(errorMsg, xhr);
                }
            }
        });
    }

    bindEvents() {
        if (this.modal) {
            this.modal.on('show.bs.modal', () => {
                this.generateTempFolder();
            });

            if (this.config.cleanupOnModalClose) {
                this.modal.on('hidden.bs.modal', () => {
                    this.cleanup();
                });
            }
        }
    }

    updateSubmitButton() {
        if (!this.submitBtn) return;

        const hasFiles = this.uploadedFiles.length > 0;
        const isUploading = this.activeUploads > 0;

        if (this.config.requireFilesForSubmit) {
            this.submitBtn.prop('disabled', !hasFiles || isUploading);
        } else {
            this.submitBtn.prop('disabled', isUploading);
        }
    }

    cleanup() {
        if (!this.tempFolder) return;

        $.ajax({
            url: this.config.deleteTempFolderRoute,
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': this.config.csrfToken,
            },
            data: JSON.stringify({ folder: this.tempFolder }),
            contentType: 'application/json',
            success: () => {},
            error: () => {}
        });

        if (this.pondInstance) {
            this.pondInstance.removeFiles();
        }

        if (this.form && this.form[0]) {
            this.form[0].reset();
        }

        this.tempFolder = null;
        this.uploadedFiles.length = 0;
        this.activeUploads = 0;
        this.updateSubmitButton();
    }

    getUploadedFiles() {
        return [...this.uploadedFiles];
    }

    getTempFolder() {
        return this.tempFolder;
    }

    removeAllFiles() {
        if (this.pondInstance) {
            this.pondInstance.removeFiles();
        }
        this.uploadedFiles.length = 0;
        this.updateSubmitButton();
    }

    addFiles(files) {
        if (this.pondInstance) {
            this.pondInstance.addFiles(files);
        }
    }

    destroy() {
        if (this.pondInstance) {
            this.pondInstance.destroy();
        }

        if (this.modal) {
            this.modal.off('show.bs.modal');
            this.modal.off('hidden.bs.modal');
        }
    }

    static create(options = {}) {
        return new FileUploadManager(options);
    }
}

if (typeof module !== 'undefined' && module.exports) {
    module.exports = FileUploadManager;
}
if (typeof window !== 'undefined') {
    window.FileUploadManager = FileUploadManager;
}
