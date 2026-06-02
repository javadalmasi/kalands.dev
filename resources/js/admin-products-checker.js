(function() {
    window.productChecker = function() {
        return {
            isWorking: false,
            shouldStop: false,
            totalCount: 0,
            processedCount: 0,
            inactiveFound: 0,
            progress: 0,
            logs: [],
            allIds: [],
            batchSize: 10,

            async init() {
                const root = document.querySelector('[data-checker-root]');
                const authkey = root?.dataset.authkey;
                const digikalaIdsUrl = root?.dataset.idsUrl;
                
                this.addLog('سیستم آماده شد. در حال دریافت لیست شناسه‌ها...', 'info');
                try {
                    const response = await fetch(digikalaIdsUrl);
                    const data = await response.json();
                    if (data.ok) {
                        this.allIds = data.ids;
                        this.totalCount = this.allIds.length;
                        this.addLog(`تعداد ${this.totalCount} محصول دیجی‌کالا برای بررسی یافت شد.`, 'success');
                    }
                } catch (e) {
                    this.addLog('خطا در دریافت لیست محصولات.', 'error');
                }
            },

            addLog(message, type = 'info') {
                const now = new Date();
                const time = now.getHours().toString().padStart(2, '0') + ':' +
                           now.getMinutes().toString().padStart(2, '0') + ':' +
                           now.getSeconds().toString().padStart(2, '0');

                this.logs.push({ time, message, type });

                this.$nextTick(() => {
                    const container = document.getElementById('log-container');
                    if (container) container.scrollTop = container.scrollHeight;
                });

                if (this.logs.length > 100) this.logs.shift();
            },

            stopChecking() {
                this.shouldStop = true;
                this.addLog('درخواست توقف ارسال شد. بعد از اتمام دسته فعلی، عملیات متوقف می‌شود.', 'error');
            },

            async startChecking() {
                if (this.isWorking) return;

                const root = document.querySelector('[data-checker-root]');
                const checkApiUrl = root?.dataset.checkUrl;
                const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

                this.isWorking = true;
                this.shouldStop = false;
                this.processedCount = 0;
                this.inactiveFound = 0;
                this.progress = 0;

                this.addLog('عملیات بررسی آغاز شد...', 'info');

                const batches = [];
                for (let i = 0; i < this.allIds.length; i += this.batchSize) {
                    batches.push(this.allIds.slice(i, i + this.batchSize));
                }

                for (let i = 0; i < batches.length; i++) {
                    if (this.shouldStop) break;

                    const batch = batches[i];
                    try {
                        const response = await fetch(checkApiUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({ product_ids: batch })
                        });

                        const result = await response.json();
                        if (result.ok) {
                            Object.keys(result.results).forEach(id => {
                                if (result.results[id].is_inactive) {
                                    this.inactiveFound++;
                                    this.addLog(`محصول ${id} غیرفعال شناسایی و در دیتابیس آپدیت شد.`, 'success');
                                }
                            });

                            this.processedCount += batch.length;
                            this.progress = Math.round((this.processedCount / this.totalCount) * 100);
                        } else {
                            this.addLog(`خطا در پردازش دسته ${i + 1}`, 'error');
                        }
                    } catch (e) {
                        this.addLog(`خطای شبکه در دسته ${i + 1}`, 'error');
                    }

                    await new Promise(r => setTimeout(r, 100));
                }

                this.isWorking = false;
                this.addLog(`عملیات به پایان رسید. بررسی ${this.processedCount} محصول انجام شد.`, 'info');
            }
        }
    };
})();
