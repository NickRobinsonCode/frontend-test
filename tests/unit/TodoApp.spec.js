import { expect } from 'chai'
import { shallowMount } from '@vue/test-utils'
import TodoApp from '@/TodoApp.vue'

describe('TodoApp.vue', () => {
  it('renders', () => {
    const msg = 'new message'
    const wrapper = shallowMount(TodoApp, {
      propsData: { showLink: true, target: 1 }
    })
    expect(wrapper.text()).to.include("Description")
  })
})