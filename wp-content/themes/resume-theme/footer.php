    </main>
    
    <footer class="site-footer">
        <div class="container">
            <div style="text-align: center; padding: 20px 0; color: #666; font-size: 12px; border-top: 1px solid #eee; margin-top: 40px;">
                <p>&copy; <?php echo date('Y'); ?> Resume Manager. Designed by 나근민 스타일.</p>
            </div>
        </div>
    </footer>
    
    <?php wp_footer(); ?>
    
    <script>
    // 동적 폼 처리 스크립트
    document.addEventListener('DOMContentLoaded', function() {
        // 스킬 추가 버튼
        const addSkillBtn = document.getElementById('add-skill');
        if (addSkillBtn) {
            addSkillBtn.addEventListener('click', function() {
                const skillsContainer = document.getElementById('skills-container');
                const skillFields = document.createElement('div');
                skillFields.className = 'skill-fields';
                skillFields.innerHTML = `
                    <div class="form-group">
                        <select name="category[]" class="form-control" required>
                            <option value="">카테고리 선택</option>
                            <option value="Programming Languages">Programming Languages</option>
                            <option value="Framework/ Library">Framework/ Library</option>
                            <option value="Server">Server</option>
                            <option value="Tooling/ DevOps">Tooling/ DevOps</option>
                            <option value="Environment">Environment</option>
                            <option value="ETC">ETC</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <input type="text" name="name[]" class="form-control" placeholder="스킬 이름" required>
                    </div>
                    <div class="form-group">
                        <select name="experience_type[]" class="form-control" required>
                            <option value="">경험 유형 선택</option>
                            <option value="experienced">사용 경험 있음</option>
                            <option value="theoretical">이론적 지식만</option>
                        </select>
                    </div>
                    <button type="button" class="btn btn-secondary remove-skill">제거</button>
                `;
                skillsContainer.appendChild(skillFields);
            });
        }

        // 스킬 제거 버튼
        document.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('remove-skill')) {
                e.target.parentElement.remove();
            }
        });

        // 파일 업로드 미리보기
        const photoInput = document.getElementById('photo');
        const photoPreview = document.getElementById('photo-preview');
        
        if (photoInput && photoPreview) {
            photoInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        photoPreview.src = e.target.result;
                        photoPreview.style.display = 'block';
                    }
                    reader.readAsDataURL(file);
                }
            });
        }

        // 프린트 버튼 추가 (선택사항)
        const printBtn = document.createElement('button');
        printBtn.innerHTML = '<i class="fas fa-print"></i> 인쇄';
        printBtn.className = 'btn btn-primary print-btn';
        printBtn.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 1000;';
        printBtn.onclick = function() {
            window.print();
        };
        
        // 인쇄 버튼을 body에 추가 (관리자 페이지가 아닐 때만)
        if (!document.body.classList.contains('admin')) {
            document.body.appendChild(printBtn);
        }
    });

    // 스무스 스크롤 (섹션 간 이동)
    function smoothScrollTo(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            element.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    }
    </script>
</body>
</html>