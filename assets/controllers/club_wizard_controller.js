import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["step1", "step2", "step3", "associationSelect", "leagueSelect", "clubName", "clubShortName", "errors"];
    static values = {
        statusUrl: String,
        associationUrl: String,
        leagueUrl: String,
        specificationUrl: String,
        resetAssociationUrl: String,
        resetLeagueUrl: String,
        csrfToken: String,
        i18n: Object
    };

    connect() {
        this.checkStatus();
    }

    async checkStatus() {
        const response = await fetch(this.statusUrlValue);
        const data = await response.json();

        if (data.showWizard) {
            this.renderStep(data);
        }
    }

    renderStep(data) {
        this.hideAllSteps();

        if (data.currentStep === 'association') {
            this.showStep1(data.associations);
        } else if (data.currentStep === 'league') {
            this.showStep2(data.leagues);
        } else if (data.currentStep === 'specification') {
            this.showStep3();
        }
    }

    hideAllSteps() {
        this.step1Target.classList.add('d-none');
        this.step2Target.classList.add('d-none');
        this.step3Target.classList.add('d-none');
        this.errorsTarget.classList.add('d-none');
        this.errorsTarget.innerHTML = '';
    }

    showStep1(associations) {
        this.step1Target.classList.remove('d-none');
        this.associationSelectTarget.innerHTML = `<option value="">${this.i18nValue.selectAssociation}</option>`;
        associations.forEach(assoc => {
            const opt = document.createElement('option');
            opt.value = assoc.uuid;
            opt.textContent = assoc.name;
            this.associationSelectTarget.appendChild(opt);
        });
    }

    showStep2(leagues) {
        this.step2Target.classList.remove('d-none');
        this.leagueSelectTarget.innerHTML = `<option value="">${this.i18nValue.selectLeague}</option>`;
        leagues.forEach(league => {
            const opt = document.createElement('option');
            opt.value = league.uuid;
            let levelText = this.i18nValue.level.replace('%level%', league.level);
            let teamCountText = this.i18nValue.teamCount.replace('%count%', league.teamCount);
            opt.textContent = `${league.name} (${levelText}) - ${teamCountText}`;
            if (league.teamCount >= 16) opt.disabled = true;
            this.leagueSelectTarget.appendChild(opt);
        });
    }

    showStep3() {
        this.step3Target.classList.remove('d-none');
    }

    async submitAssociation() {
        const associationId = this.associationSelectTarget.value;
        const response = await fetch(this.associationUrlValue, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ associationId })
        });
        const result = await response.json();

        if (result.success) {
            this.refreshStatus();
        } else {
            this.showErrors(result.errors);
        }
    }

    async submitLeague() {
        const leagueId = this.leagueSelectTarget.value;
        const response = await fetch(this.leagueUrlValue, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ leagueId })
        });
        const result = await response.json();

        if (result.success) {
            this.refreshStatus();
        } else {
            this.showErrors(result.errors);
        }
    }

    async submitSpecification() {
        const name = this.clubNameTarget.value;
        const shortName = this.clubShortNameTarget.value;
        const response = await fetch(this.specificationUrlValue, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                name,
                shortName,
                _token: this.csrfTokenValue
            })
        });
        const result = await response.json();

        if (result.success && result.completed) {
            window.location.reload();
        } else {
            this.showErrors(result.errors);
        }
    }

    async backToStep1() {
        await fetch(this.resetAssociationUrlValue, { method: 'POST' });
        this.hideAllSteps();
        this.refreshStatus();
    }

    async backToStep2() {
        await fetch(this.resetLeagueUrlValue, { method: 'POST' });
        this.hideAllSteps();
        this.refreshStatus();
    }

    async refreshStatus() {
        const response = await fetch(this.statusUrlValue);
        const data = await response.json();
        this.renderStep(data);
    }

    showErrors(errors) {
        this.errorsTarget.classList.remove('d-none');
        this.errorsTarget.innerHTML = '';
        Object.values(errors).forEach(fieldErrors => {
            fieldErrors.forEach(error => {
                const div = document.createElement('div');
                div.textContent = error;
                this.errorsTarget.appendChild(div);
            });
        });
    }
}
