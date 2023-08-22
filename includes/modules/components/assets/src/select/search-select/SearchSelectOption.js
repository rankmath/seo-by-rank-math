import '../../../scss/search-select-option.scss'

export default function ({ title, subTitle, description }) {
  return (
    <div className='option__container'>
      <div className='option__heading'>
        <h1 className='option__heading-title'>{title}</h1>

        <p className='option__heading-subtitle'>{subTitle}</p>
      </div>


      <p className='option__description'>{description}</p>
    </div>
  )
}